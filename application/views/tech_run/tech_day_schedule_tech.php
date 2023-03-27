<style>
.col-mdd-3{
	max-width:12.5%;
	
}
.atoa .ato{
	padding-left: 10px;
	padding-right: 10px;
}
.ato_input{width:115px;}
.ato_a{padding-right:10px;padding-top:10px;}
.ato_a, .ato_input{
	float:left;
}
.ato_text{padding-top:10px;}
.jobs-completed_block{
	position: relative;
	top: 9px;
}
.jobs-completed_block span#jobs_count_span{
	color:#b4151b;
}
.jobs-completed_block span#jobs_completed_count_span{
	color:green;
}
.top_more_info_box{
	margin-bottom:15px;
}
.time_div_toggle,
#more_tr_notes_fb{
	display:none;
}
.key_num_span{
	display: none;
}
img.img_pnotes,
img.key_icon {
	cursor: pointer;
}

/* keys lightbox css */
.agency_keys_lb{
	width: 80%;
}
.fancybox-content {
    max-width: unset;
}
.job_reason_div .form-control{
	margin: 5px 0;
}
.key_num_th{
	width: 15%;
}
.is_keys_picked_up_yes:checked + label::after,
.is_keys_returned_yes:checked + label::after{
	background: #00e600 !important;	
}
.is_keys_picked_up_no:checked + label::after,
.is_keys_returned_no:checked + label::after{
	background: #ff0000 !important;	
}
.agency_keys_lb .second_div .row{
	margin-bottom: 14px;
}
.agency_keys_lb .second_div label {
    position: relative;
    top: 11px;
}
.paddress_th{
	width: 40%;
}
.keys_picked_up_th{
	width: 40%;
}
/* about text */
.about_page_li li {
    padding-top: 15px;
}
.about_page_li {
    margin-bottom: 20px;
}
.about_page_li .row_icons{
	width: 20px;
}
.signature_div {
    margin-bottom: 20px;
	display:none;
}
.number_of_keys {
    width: 70px;    
    float: left;
}

.agency_staff {
	width: 35.3%;
	float: left;
	margin-right: 5px;
}
#btn_clear_signature{
	display: none
}
.signature_svg_img{
	width: 150px
}
.ppe_icon {
	position: relative;
	left: 7px;
	bottom: 2px;
}

#more_tr_notes_fb{
	margin: 1px 100px;
}
</style>
<div class="box-typical box-typical-padding">

	<?php 
	// breadcrumbs template
	$bc_items = array(
		array(
			'title' => "{$title}",
			'status' => 'active',
			'link' => "/tech_run/run_sheet/{$this->uri->segment(3)}"
		)
	);
    $bc_data['bc_items'] = $bc_items;

    if($staff_classID!=6){ //admin link if not tech
        $bc_data['has_admin_version'] = 1;
		$bc_data['has_admin_version_url'] = "/tech_run/run_sheet_admin/{$this->uri->segment(3)}";
		$bc_data['staff_classID'] = $staff_classID;			
    }

	$this->load->view('templates/breadcrumbs', $bc_data);
	?>

	<header class="box-typical-header">

		<div class="box-typical box-typical-padding">

			<div class="row">

				<div class="col-md-4 text-left">
					<button type="button" id="kms_update" class="btn">KMs</button>	
					<a href="/stock/update_tech_stock/<?php echo $staff['StaffID'] ?>">
						<button type="button" class="btn">Stock</button>		
					</a>
				</div>

				<span class="col-md-4 text-center">
					<div>Jobs (<span id="jobs_completed_count_span"><?php $comp_count ?></span>/<span id="jobs_count_span"><?php echo $jr_count ?></span>)</div>
					<div class="col text-center">
						<h5 class="mb-0 mt-2">Tech run notes:</h5>
						<?php 
						$max_length = 200;
						echo ( strlen($tr['notes']) > $max_length)?substr($tr['notes'],0,$max_length)." <a  id='more_tr_notes' href='javascript:void(0)'>[MORE]</a>":$tr['notes'];
						?>
					</div>						
				</span>

				<div class="col-md-4 text-right">

					<button type="button" class="btn <?php echo ( $tb_count > 0 )?'btn-success':null; ?>" id="take_lunch_break_btn">
						Lunch Break
					</button>

					<a href="/tech_run/run_sheet_simple/?tr_id=<?php echo $tr_id; ?>">
						<button type="button" class="btn">Simple</button>		
					</a>

					<a href="/tech_run/available_dk/?tr_id=<?php echo $tr_id; ?>">
						<button type="button" class="btn">DKs</button>		
					</a>

					<a href="/tech_run/run_sheet_map/?tr_id=<?php echo $tr_id; ?>">
						<button type="button" class="btn">Map</button>		
					</a>

				</div>	

			</div>

		</div>
	</header>

	<section>

		<div class="body-typical-body">
			<div class="table-responsive">
				<?php
				
				// TDS table
				$tr_table_data_view = array(
					'tech_id' => $tech_id,
					'date' => $date,
					
					'accom_name' => $accom_name,
					'start_agency_address' => $start_agency_address,
					'end_accom_name' => $end_accom_name,
					'end_agency_address' => $end_agency_address,
					'is_email' => $is_email
				);
				$this->load->view('tech_run/tech_day_schedule_tech_table_list',$tr_table_data_view);		
				?>
				
				

				<div id="mbm_box" class="text-right" style="display: none;">
					<div class="gbox_main">
						<div class="gbox form-group">
							<button id="btn_rebook" type="button" class="btn btn-danger">Rebook</button>
						</div>
					</div>
				</div>
				
			</div>

			

			<nav aria-label="Page navigation example" style="text-align:center">
				<?php echo $pagination; ?>
			</nav>

			<div class="pagi_count text-center">
				<?php echo $pagi_count; ?>
			</div>

		</div>
	</section>

</div>

<!-- Fancybox Start -->

<!-- about page -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>						
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<?php $this->load->view('tech_run/tech_day_schedule_tech_about_page'); ?>

</div>


<!-- pick up -->
<!--<a href="javascript:void(0);" id="pick_up_fb_trigger" class="fb_trigger agency_keys_lb_trigger jfancybox" data-fancybox data-src="#pick_up_fb">Trigger the fancybox</a>-->
<div id="pick_up_fb" class="jfancybox agency_keys_lb" style="display:none;">

	<h2 class="keys_lb_agency_name"></h2>
	
	<div id="pick_up_lb_content" class="agency_keys_lb_content"></div>

	<div class="second_div">			
		<div class="row">

			<div class="col-md-12">
				<input type="text" class="form-control agency_staff" placeholder="Agency Staff" />
				<input type="number" class="form-control number_of_keys" placeholder="No. of Keys" />
				<button type='button' class='btn btn-success float-right' id="save_pick_up_btn">Save Pickup</button>
			</div>		

		</div>
	</div>


	<input type="hidden" class="trk_id" />
	<input type="hidden" class="tech_id" />
	<input type="hidden" class="date" />
	<input type="hidden" class="agency_id" />	

</div>


<!-- drop off -->
<!--<a href="javascript:void(0);" id="drop_off_fb_trigger" class="fb_trigger agency_keys_lb_trigger jfancybox" data-fancybox data-src="#drop_off_fb">Trigger the fancybox</a>-->							
<div id="drop_off_fb" class="jfancybox agency_keys_lb" style="display:none;" >

	<h2 class="keys_lb_agency_name"></h2>
	
	<div id="drop_off_lb_content" class="agency_keys_lb_content"></div>

	<div class="second_div">			
		<div class="row">

			<div class="col-md-12">
				<input type="text" class="form-control agency_staff" placeholder="Agency Staff" />
				<input type="number" class="form-control number_of_keys" placeholder="No. of Keys" />
			</div>	

		</div>
	</div>

	<div class="row">
		<div class="col-md-12">
			<label class="mb-2">Signature: </label>
			<img class="signature_svg_img" />
		</div>
	</div>
	
	
	<div class="signature_div"> 
		<h1 style="text-align: center; color: red; font-style: italic;">Please sign on the line below</h1> 
		<div id="signature" style="border: 1px solid red; margin-bottom: 5px;"></div>													
	</div>

	<div class="mb-4">			
		<div class="checkbox">
			<input type="checkbox" id="refused_sig" class="refused_sig" value="1" />
			<label for="refused_sig">Agent requests NOT to sign for keys due to COVID</label>
		</div>
	</div>
	

	<input type="hidden" class="trk_id" />
	<button type="button" class="btn btn-danger float-left" id="btn_clear_signature">Clear Signature</button>	
	<button type='button' class='btn btn-success float-right' id="save_drop_off_btn">Save Drop Off</button>

</div>

<div id="more_tr_notes_fb" class="fancybox text-center">
	<h4>Tech run notes:</h4>	
	<?php echo $tr['notes']; ?>
</div>

<!-- Fancybox END -->

<script type="text/javascript" src="https://crmdev.sats.com.au/js/jquery.tablednd_0_5.js"></script>
<script type="text/javascript">
// signature instance
var sigdiv = jQuery("#signature");

// clear signature
function clearSignature(sigdiv){
	sigdiv.jSignature("reset"); // clears the canvas and rerenders the decor on it.
}


// save signature
function saveSignature(sigdiv){
	// Getting signature as SVG and rendering the SVG within the browser. 
	// (!!! inline SVG rendering from IMG element does not work in all browsers !!!)
	// this export plugin returns an array of [mimetype, base64-encoded string of SVG of the signature strokes]
	var datapair = sigdiv.jSignature("getData", "svgbase64") 
	var i = new Image();
	var svg_txt = "data:" + datapair[0] + "," + datapair[1];
	i.src = svg_txt;
	return svg_txt;
}

// invoke digital signature
function invoke_jsignature(){
	
	sigdiv.jSignature({ 
		width: '100%',
		height: '250px'
	});

}

jQuery(document).ready(function(){

	//LOAD FUNCTIONS ON LOAD START

	jobsCompletedCount();

	//LOAD FUNCTIONS ON LOAD END

	// clear signature
    jQuery("#btn_clear_signature").click(function(){
				
		clearSignature(sigdiv);
		
	});


	
	// take lunch break script
	jQuery("#take_lunch_break_btn").click(function(){

		var take_lunch_break_btn = jQuery(this);

		if( take_lunch_break_btn.hasClass('btn-success') == false ){
			
			$('#load-screen').show(); 
			jQuery.ajax({
				type: "POST",
				url: "/tech_run/take_lunch_break",
				data: { 											
					tech_id: '<?php echo $tech_id; ?>'
				}
			}).done(function( ret ){

				$('#load-screen').hide(); 					
				take_lunch_break_btn.addClass("btn-success");

			});	

		}	
					

	});


	// pick up lightbox
	jQuery(".pick_up_btn").click(function(){

		// clear drop off lightbox
		jQuery("#drop_off_lb_content").html("");

		var pick_up_btn = jQuery(this);	
		var keys_fb_div = jQuery("#pick_up_fb");			

		var trk_id = pick_up_btn.attr("data-trk_id");
		var tech_id = pick_up_btn.attr("data-tech_id");
		var date = pick_up_btn.attr("data-date");
		var agency_id = pick_up_btn.attr("data-agency_id");
		var agency_name = pick_up_btn.attr("data-agency_name");
		var completed = pick_up_btn.attr("data-completed");		
		var agency_staff = pick_up_btn.attr("data-agency_staff");
		var number_of_keys = pick_up_btn.attr("data-number_of_keys");
		
		$('#load-screen').show(); 
		jQuery.ajax({
			type: "POST",
			url: "/tech_run/ajax_job_key_list",
			data: { 						
				tech_id: tech_id,
				date: date,
				agency_id: agency_id,
				key_action: 'Pick Up'
			}
		}).done(function( ret ){

			$('#load-screen').hide(); 

			keys_fb_div.find(".keys_lb_agency_name").html(agency_name);
			keys_fb_div.find(".trk_id").val(trk_id);
			keys_fb_div.find(".tech_id").val(tech_id);
			keys_fb_div.find(".date").val(date);
			keys_fb_div.find(".agency_id").val(agency_id);

			keys_fb_div.find(".agency_staff").val(agency_staff);
			keys_fb_div.find(".number_of_keys").val(number_of_keys);

			keys_fb_div.find(".agency_keys_lb_content").html(ret); // load pick up content
			//jQuery("#pick_up_fb_trigger").click(); // trigger lightbox

			$.fancybox.open({
				src  : '#pick_up_fb',
				touch : false
			}); 

		});				

	});


	// drop off lightbox
	jQuery(".drop_off_btn").click(function(){

		// clear pick up lightbox
		jQuery("#pick_up_lb_content").html("");

		var pick_up_btn = jQuery(this);	
		var keys_fb_div = jQuery("#drop_off_fb");			

		var trk_id = pick_up_btn.attr("data-trk_id");
		var tech_id = pick_up_btn.attr("data-tech_id");
		var date = pick_up_btn.attr("data-date");
		var agency_id = pick_up_btn.attr("data-agency_id");
		var agency_name = pick_up_btn.attr("data-agency_name");
		var agency_staff = pick_up_btn.attr("data-agency_staff");
		var number_of_keys = pick_up_btn.attr("data-number_of_keys");		
		var signature_svg = pick_up_btn.attr("data-signature_svg");
		var refused_sig = pick_up_btn.attr("data-refused_sig");

		$('#load-screen').show(); 
		jQuery.ajax({
			type: "POST",
			url: "/tech_run/ajax_job_key_list",
			data: { 						
				tech_id: tech_id,
				date: date,
				agency_id: agency_id,
				key_action: 'Drop Off'
			}
		}).done(function( ret ){

			$('#load-screen').hide(); 

			keys_fb_div.find(".keys_lb_agency_name").html(agency_name);
			keys_fb_div.find(".trk_id").val(trk_id);

			keys_fb_div.find(".agency_staff").val(agency_staff);
			keys_fb_div.find(".number_of_keys").val(number_of_keys);
			

			keys_fb_div.find(".agency_keys_lb_content").html(ret); // load pick up content
			//jQuery("#drop_off_fb_trigger").click(); // trigger lightbox
			
			$.fancybox.open({
				src  : '#drop_off_fb',
				touch : false,
				afterLoad : function(instance, current) {

					if( signature_svg == '' ){ // singature is empty

						keys_fb_div.find(".signature_div").show();
						keys_fb_div.find("#btn_clear_signature").show();

						jQuery("#signature").html("");
						invoke_jsignature();

					}else{ // has saved signature

						keys_fb_div.find(".signature_div").hide();
						keys_fb_div.find("#btn_clear_signature").hide();

						jQuery("#signature").html("");
						//invoke_jsignature();

						keys_fb_div.find(".signature_svg_img").attr("src",signature_svg);

					}					


				}
			}); 

			if( refused_sig == 1 ){
				keys_fb_div.find(".refused_sig").prop("checked",true);
			}else{
				keys_fb_div.find(".refused_sig").prop("checked",false);
			}			

		});				

	});



	// job reason toggle  
	jQuery(".agency_keys_lb_content").on("change",".is_keys_picked_up",function(){

		var node = jQuery(this);
		var is_keys_picked_up = node.val();    
		var parent_tr = node.parents("tr:first");				

		if( is_keys_picked_up == 0 ){
			parent_tr.find(".job_reason_div").show();
		}else{
			parent_tr.find(".job_reason_div").hide();
		}

	});  	



	// save pick up
	jQuery(".agency_keys_lb").on("click","#save_pick_up_btn",function(){

		var pick_up_btn = jQuery(this);
		var parent_div = pick_up_btn.parents("#pick_up_fb");

		var trk_id = parent_div.find(".trk_id").val();	
		var tech_id = parent_div.find(".tech_id").val();
		var date = parent_div.find(".date").val();
		var agency_id = parent_div.find(".agency_id").val();

		var agency_staff = parent_div.find(".agency_staff").val();
		var number_of_keys = parent_div.find(".number_of_keys").val();		

		var key_number_arr = [];
		var job_id_arr = [];
		var is_keys_picked_up_arr = [];	
		var attend_property_arr = [];
		var job_reason_arr = [];			
		var reason_comment_arr = [];	
			
		var error = "";
		var no_is_keys_picked_up_flag = 0;
		var no_attend_property_flag = 0;
		var no_reason_dp = 0;

		// get all job ID
		parent_div.find(".job_id").each(function(){

			var node = jQuery(this);
			var parents_tr = node.parents("tr:first");

			var job_id = node.val();
			job_id_arr.push(job_id);

			// key number
			var key_number = parents_tr.find(".key_number").val();
			key_number_arr.push(key_number);

			// attend property?
			var attend_property = parents_tr.find(".attend_property").val();
			attend_property_arr.push(attend_property);

			// key is pick up
			var is_keys_picked_up = parents_tr.find(".is_keys_picked_up:checked").val();
			is_keys_picked_up_arr.push(is_keys_picked_up);

			// make key pick up required
			if( is_keys_picked_up == "" || is_keys_picked_up == undefined ){
				no_is_keys_picked_up_flag = 1;
			}
			
			// make attend property required if key picked up is NO
			if( is_keys_picked_up == 0 ){				
				if( attend_property == "" || attend_property == undefined ){
					no_attend_property_flag = 1;
				}
			}			

			// job reason
			var job_reason = parents_tr.find(".job_reason").val();
			job_reason_arr.push(job_reason);

			// make reason dp required if attend property is NO
			if( attend_property == 0 ){
				if( job_reason == "" || job_reason == undefined ){
					no_reason_dp = 1;
				}
			}

			// reason comment
			var reason_comment = parents_tr.find(".reason_comment").val();
			reason_comment_arr.push(reason_comment);

		});	

		if( agency_staff == "" ){
			error += "Agency staff is required\n";
		}

		if( number_of_keys == "" ){
			error += "Number of keys is required\n";
		}

		if( no_is_keys_picked_up_flag == 1 ){
			error += "Please answer 'Yes' or 'No' for each key\n";
		}

		if( no_attend_property_flag == 1 ){
			error += "Please tell us if you'll still attend the property\n";
		}

		if( no_reason_dp == 1 ){
			error += "Please give a reason you won't attend\n";
		}

		// save pick up info
		if( error != '' ){

			swal({
				title: "Warning!",
				text: error,
				type: "warning",
				confirmButtonClass: "btn-success",
				showConfirmButton: true
			});

		}else{

			swal({
				title: "Warning!",
				text: "This will save the current pick up info. Do you want to continue?",
				type: "warning",						
				showCancelButton: true,
				confirmButtonClass: "btn-success",
				confirmButtonText: "Yes, Continue",
				cancelButtonClass: "btn-danger",
				cancelButtonText: "No, Cancel!",			
				showLoaderOnConfirm: true
			},
			function(isConfirm) {

				if (isConfirm) {							  
					
					$('#load-screen').show(); 				
					jQuery.ajax({
						type: "POST",
						url: "/tech_run/ajax_save_agency_key_pickup",
						data: { 
							trk_id: trk_id,

							tech_id: tech_id,
							date: date,
							agency_id: agency_id,

							agency_staff: agency_staff,
							number_of_keys: number_of_keys,

							key_number_arr: key_number_arr,
							job_id_arr: job_id_arr,	
							is_keys_picked_up_arr: is_keys_picked_up_arr,
							attend_property_arr: attend_property_arr,
							job_reason_arr: job_reason_arr,				
							reason_comment_arr: reason_comment_arr
						}
					}).done(function( ret ){

						$('#load-screen').hide(); 	

						
						swal({
							title: "Success!",
							text: "Pick up key info saved",
							type: "success",
							confirmButtonClass: "btn-success",
							showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
							timer: <?php echo $this->config->item('timer') ?>
						});
						setTimeout(function(){ location.reload(); }, <?php echo $this->config->item('timer') ?>);	
						

								

					});						

				}

			});

		}								


	}); 



	// save drop off
	jQuery(".agency_keys_lb").on("click","#save_drop_off_btn",function(){

		var pick_up_btn = jQuery(this);
		var parent_div = pick_up_btn.parents("#drop_off_fb");

		var trk_id = parent_div.find(".trk_id").val();	

		var agency_staff = parent_div.find(".agency_staff").val();
		var number_of_keys = parent_div.find(".number_of_keys").val();		

		var sigdiv = jQuery("#signature");
		var saved_sig_img = jQuery(".signature_svg_img").attr("src");
		if( typeof saved_sig_img === "undefined" ){  // no saved signature
			var signature_svg = saveSignature(sigdiv); // capture signature
		}else{
			var signature_svg = '';
		}

		var refused_sig = ( parent_div.find(".refused_sig").prop("checked") == true )?1:0;
		

		var agency_keys_id_arr = [];
		var is_keys_returned_arr = [];
		var not_returned_notes_arr = [];

		var error = "";
		var no_is_keys_returned_flag = 0;
		var no_not_returned_notes_flag = 0;

		// get all job ID
		parent_div.find(".job_id").each(function(){

			var node = jQuery(this);
			var parents_tr = node.parents("tr:first");			

			// agency keys ID?
			var agency_keys_id = parents_tr.find(".agency_keys_id").val();
			agency_keys_id_arr.push(agency_keys_id);

			// key hidden is pick up value
			var is_keys_picked_up = parents_tr.find(".is_keys_picked_up").val();			

			// is key returned?
			var is_keys_returned = parents_tr.find(".is_keys_returned:checked").val();
			is_keys_returned_arr.push(is_keys_returned);
			
			// reason comment
			var not_returned_notes = parents_tr.find(".not_returned_notes").val();
			not_returned_notes_arr.push(not_returned_notes);

			// make keys returned required
			if( is_keys_picked_up == 1 && ( is_keys_returned == "" || is_keys_returned == undefined ) ){
				no_is_keys_returned_flag = 1;
			}
			
			// if keys pick up = YES and keys returned =  NO and key not returned note is empty
			if( is_keys_picked_up == 1 && is_keys_returned == 0 && not_returned_notes == '' ){				
				no_not_returned_notes_flag = 1;
			}

		});	

		if( agency_staff == "" ){
			error += "Agency staff is required\n";
		}

		if( number_of_keys == "" ){
			error += "Number of keys is required\n";
			
		}

		if( no_is_keys_returned_flag == 1 ){
			error += "Please answer 'Yes' or 'No' if keys are returned\n";
		}

		if( no_not_returned_notes_flag == 1 ){
			error += "Please explain your 'Other' drop off.\n";
		}

		if( typeof saved_sig_img === "undefined" ){ // no saved signature

			// signature required validation; except refused signature
			if( sigdiv.jSignature('getData', 'native').length == 0 && refused_sig != 1 ) {
				error += "Signature is required\n";
			}

		}
		
		

		// save drop off info
		if( error != '' ){

			swal({
				title: "Warning!",
				text: error,
				type: "warning",
				confirmButtonClass: "btn-success",
				showConfirmButton: true
			});

		}else{

			swal({
				title: "Warning!",
				text: "This will save the current drop off info. Do you want to continue?",
				type: "warning",						
				showCancelButton: true,
				confirmButtonClass: "btn-success",
				confirmButtonText: "Yes, Continue",
				cancelButtonClass: "btn-danger",
				cancelButtonText: "No, Cancel!",			
				showLoaderOnConfirm: true
			},
			function(isConfirm) {

				if (isConfirm) {							  
					
					$('#load-screen').show(); 
					jQuery.ajax({
						type: "POST",
						url: "/tech_run/ajax_save_agency_key_drop_off",
						data: { 
							trk_id: trk_id,
							agency_staff: agency_staff,
							number_of_keys: number_of_keys,	
							signature_svg: signature_svg,
							refused_sig: refused_sig,						

							agency_keys_id_arr: agency_keys_id_arr,		
							is_keys_returned_arr: is_keys_returned_arr,							
							not_returned_notes_arr: not_returned_notes_arr
						}
					}).done(function( ret ){

						$('#load-screen').hide(); 	
						swal({
							title: "Success!",
							text: "Drop off key info saved",
							type: "success",
							confirmButtonClass: "btn-success",
							showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
							timer: <?php echo $this->config->item('timer') ?>
						});
						setTimeout(function(){ location.reload(); }, <?php echo $this->config->item('timer') ?>);			

					});							

				}

			});

		}								


	}); 








	// display agency or supplier name
	jQuery(".agency_name_link").click(function(){

		var agency_address = jQuery(this).parents("td:first").find(".agency_address_txt").val();				
		swal({
			title:"",
			text: agency_address,
			type: "info",
			showCancelButton: false,
			confirmButtonText: "OK",
			closeOnConfirm: true,
			showConfirmButton: true
		});

	});



	$('#check-all').on('change',function(){
		var obj = $(this);
		var isChecked = obj.is(':checked');
		var divbutton = $('#mbm_box');
		if(isChecked){
			divbutton.show();
			$('.check_box').prop('checked',true);
		}else{
			divbutton.hide();
			$('.check_box').prop('checked',false);
		}
	})

	$('.check_box').on('change',function(){
		var obj = $(this);
		var isLength = $('.check_box:checked').length;
		var divbutton = $('#mbm_box');
		if(isLength>0){
			divbutton.show();
		}else{
			divbutton.hide();
		}
	})


	$('#btn_rebook').click(function(e){
		e.preventDefault();
		
		var job_id = new Array();
		jQuery(".check_box:checked").each(function(){
			job_id.push(jQuery(this).val());
		});
		var checkbox_legth = $('.check_box:checked').length;

		var err = "";

		if(checkbox_legth<=0){
			err += "Atleast 1 job must be selected \n";
		}

		if(err!=""){
			swal('',err,'error');
			return false;
		}

		swal(
			{
				title: "",
				text: "Are You Sure You Want to Continue?",
				type: "warning",
				showCancelButton: true,
				confirmButtonClass: "btn-success",
				confirmButtonText: "Yes",
				cancelButtonClass: "btn-danger",
				cancelButtonText: "No, Cancel!",
				closeOnConfirm: false,
				closeOnCancel: true,
			},
			function(isConfirm){
				if(isConfirm){

					$('#load-screen').show(); //show loader

					jQuery.ajax({
					type: "POST",
					url: "<?php echo base_url('/tech_run/ajax_rebook_script') ?>",
					dataType: 'json',
					data: { 
						job_id: job_id,
						is_240v: 0
					}
					}).done(function(data){
					
						if(data.status){

							$('#load-screen').hide(); //hide loader
							swal({
								title:"Success!",
								text: "Rebook success",
								type: "success",
								showCancelButton: false,
								confirmButtonText: "OK",
								closeOnConfirm: false,
								showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
								timer: <?php echo $this->config->item('timer') ?>

							});
							setTimeout(function(){ window.location='/tech_run/run_sheet/<?php echo $tr_id; ?>'; }, <?php echo $this->config->item('timer') ?>);	
							
						}

					});

				}
				
			}
		);

	})


	//KMS
	$('.btn-kms').click(function(){

		var kms = $('#kms').val();
		var vehicles_id = $('#vehicles_id').val();

		var err = "";
		
		if(kms==""){
			err += "KMS must not be empty \n";
		}

		if(err!=""){
			swal('',err,'error');
			return false;
		}

			$('#load-screen').show();
		jQuery.ajax({
			type: "POST",
			url: "/tech_run/ajax_add_kms",
			dataType: 'json',
			data: { 
				kms: kms,
				vehicles_id: vehicles_id
			}
			}).done(function( ret ) {
				$('#load-screen').hide();

				if(ret.status){

					swal({
						title:"Success!",
						text: "KMS Successfully Added",
						type: "success",
						showCancelButton: false,
						confirmButtonText: "OK",
						closeOnConfirm: false,
						showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
						timer: <?php echo $this->config->item('timer') ?>
					});	
					
					var full_url = window.location.href;
					setTimeout(function(){ window.location=full_url }, <?php echo $this->config->item('timer') ?>);	

				}else{

					swal('','Server error please contact admin.','error');

				}	

			});	

		})


		jQuery(".img_call_before").click(function(){
			jQuery(this).parents("tr:first").find(".booked_with_tenant_div").toggle();
		});

		// key num toggle
		jQuery(".key_icon").click(function(){					
			jQuery(this).parents("tr:first").find(".key_num_span").toggle();
		});


		<?php
			if( $hasTechRun == true ){ ?>
				getTechRunNewLists(1);
			<?php	
			}
		?>


	jQuery(".img_pnotes").click(function(){
			
		jQuery(this).parents("tr:first").find(".property_notes_div").toggle();
		
	});


	}) //doc ready end





	// jobs completed count script
	function jobsCompletedCount(){
	var comp_count = jQuery("#comp_count").val();
	var jobs_count = jQuery("#jobs_count").val();

	jQuery("#jobs_completed_count_span").html(comp_count);
	jQuery("#jobs_count_span").html(jobs_count);
	}



	function getTechRunNewLists(gao){

	$('#load-screen').show(); 
	jQuery.ajax({
		type: "POST",
		url: "/tech_run/ajax_tech_run_get_new_list",
		data: { 
			tr_id: '<?php echo $tr_id; ?>',
			tech_id: '<?php echo $tech_id; ?>',
			date: '<?php echo $date; ?>',
			sub_regions: '<?php echo $sub_regions; ?>',
			get_assigned_only: gao
		}
	}).done(function( ret ){
		
		$('#load-screen').hide(); 
		//console.log('new jobs: '+ret);
		var msg = '';
		
		if(parseInt(ret)>0){
			swal(
				{
					title: "",
					text: "New Jobs Found!\nWe are refreshing the page",
					type: "warning",
					showCancelButton: false,
					confirmButtonText: "OK",
					closeOnConfirm: false,
					showConfirmButton: <?php echo $this->config->item('showConfirmButton') ?>,
					timer: <?php echo $this->config->item('timer') ?>
				}
							
			);

			var full_url = window.location.href;
			setTimeout(function(){ window.location=full_url }, <?php echo $this->config->item('timer') ?>);	

		}else{
			//msg = 'No New Jobs Found';
		}
		
	});

	}


	jQuery(".agency_keys_lb_content").on("change",".attend_property",function(){

		var node = jQuery(this);
		var parents = node.parents(".job_reason_div:first");
		
		var attend_property = node.val();

		console.log("attend_property: "+attend_property);

		if( attend_property == 0 ){ // YES
			parents.find(".not_completed_div").show();
		}else{
			parents.find(".not_completed_div").hide();
		}

		

	});


	// is keys returned toggle  
	jQuery(".agency_keys_lb_content").on("change",".is_keys_returned",function(){

		var node = jQuery(this);
		var is_keys_returned = node.val();    
		var parent_tr = node.parents("tr:first");				

		if( is_keys_returned == 0 ){
			parent_tr.find(".keys_not_returned_div").show();
		}else{
			parent_tr.find(".keys_not_returned_div").hide();
		}

	}); 	


	// launch fancybox via js
	jQuery("#more_tr_notes").click(function(){

		jQuery.fancybox.open({
			src  : '#more_tr_notes_fb'
		});

	});


</script>



