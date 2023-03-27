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
	padding-top:10px;
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
.time_div_toggle{
	display:none;
}
.key_num_span{
	display: none;
}
img.img_pnotes,
img.key_icon {
	cursor: pointer;
}
.job_reason_div,
#process_div{
	display: none;
}

.row_icons{
	width: 24px !important;
}
.pdf_icon{
	font-size: 24px;
	position: relative;
	top: 5px;
	left: 3px;
}
</style>
<div class="box-typical box-typical-padding">

	<?php
	// breadcrumbs template
	$bc_items = array(
		array(
			'title' => "Run Sheet",
			'link' => "/tech_run/run_sheet_admin/{$this->input->get_post('tr_id')}"
		),
		array(
			'title' => "{$title}",
			'status' => 'active',
			'link' => "{$uri}/?tr_id={$this->input->get_post('tr_id')}"
		)
	);
    $bc_data['bc_items'] = $bc_items;

	$this->load->view('templates/breadcrumbs', $bc_data);
	?>

	<div id="map-canvas" style="width:100%;height:500px;border:1px solid #cccccc;"></div>

	<section>

		<div class="body-typical-body">
			<div class="table-responsive">




<table id="tbl_maps" class="table main-table tds_tbl">
<thead>
	<tr>
		<th>#</th>
		<th>Service</th>
		<th>Details</th>
		<th>
			<?php
			//if( $this->config->item('country') == 2 ){ // NZ only 
			?>
				Alarm
			<?php
			//}
			?>
		</th>
		<th>Ladder</th>
		<th>Address</th>
		<th>Key #</th>
		<th>Notes</th>
		<th>Agent</th>
		<th>Age</th>
		<th>Job #</th>
		<th class="text-right">
            <div class='checkbox'>
                <input type='checkbox' id='job_id_chk_all' class='job_id_chk_all' />
                <label for='job_id_chk_all'>&nbsp;</label>
            </div>
        </th>
	</tr>
</thead>

<tbody>

	<?php
	$j = 1;
	$comp_count = 0;
	$jobs_count = 0;
	foreach($jr_list2->result_array() as $row){

		$hiddenText = "";
		$showRow = 1;
		$isUnavailable = 0;


	?>

		<?php

			// ROW IS JOBS
			if( $row['row_id_type'] == 'job_id' ){


				$jr_sql = $this->tech_model->getJobRowData($row['row_id'],$this->config->item('country'));
				$row2 = $jr_sql->row_array();


				// if job type is 240v Rebook and status is to be booked and the tech is not electricianthen hide it
				if( ( $row2['job_type']=='240v Rebook' || $row2['is_eo']== 1 ) && $row2['j_status']=='To Be Booked' && $isElectrician==false ){
					$hiddenText .= '240v<br />';
					$showRow = 0;
				}else{
					$showRow = 1;
				}

				if( $row['hidden']==1 ){
					$hiddenText .= 'User<br />';
				}

				if( $row2['unavailable']==1 && $row2['unavailable_date']==$date ){
					$isUnavailable = 1;
					$hiddenText .= 'Unavailable<br />';
				}

				$startDate = date('Y-m-d',strtotime($row2['start_date']));

				if( $row2['job_type'] == 'Lease Renewal' && ( $row2['start_date']!="" && $date < $startDate ) ){
					$hiddenText .= 'LR<br />';
				}

				if( $row2['job_type'] == 'Change of Tenancy' && ( $row2['start_date']!="" && $date < $startDate  ) ){
					$hiddenText .= 'COT<br />';
				}

				if( $row2['j_status'] == 'DHA' && ( $row2['start_date']!="" && $date < $startDate ) ){
					$hiddenText .= 'DHA<br />';
				}

				if( $row2['j_status'] == 'On Hold' && ( $row2['start_date']!="" && $date < $startDate ) ){
					$hiddenText .= 'On Hold<br />';
				}

				if( $row2['j_status'] == 'On Hold' && $row['allow_upfront_billing']==1 ){
					$hiddenText .= 'Up Front Billing<br />';
				}

				/*
				// this job is for electrician only
				if( $row2['electrician_only'] == 1 && $isElectrician == false ){
					$hiddenText .= 'Electrician Only<br />';
				}
				*/

				if( $show_hidden==0 && $hiddenText!="" && $row2['j_status']!='Booked' ){
					$showRow = 0;
				}else{
					$showRow = 1;
				}


				$bgcolor = "#FFFFFF";


				if($row2['job_reason_id']>0){
					$bgcolor = "#fffca3";
				}else if($row2['ts_completed']==1){
					$bgcolor = "#c2ffa7";
				}


				/*if($row2['ts_completed']==1){
					$bgcolor = "#c2ffa7";
				}
				*/

				$j_created = date("Y-m-d",strtotime($row2['created']));
				$last_60_days = date("Y-m-d",strtotime("-60 days"));


				if( $hiddenText!="" ){
					$hiddenRowsCount++;
					//$bgcolor = "#ADD8E6";
					$isHidden = 1;
				}

				if( $show_hidden==1 && ( $row['hidden']==1 || $isUnavailable==1 ) ){
					$hideChk = 0;
				}else if( $show_hidden==1 ){
					$hideChk = 1;
				}else{
					$hideChk = 0;
				}


				if( $row['highlight_color']!="" ){
					//$bgcolor = $row['highlight_color'];
				}


				// priority jobs
				if(
					$row2['job_type'] == "Change of Tenancy" ||
					$row2['job_type'] == "Lease Renewal" ||
					$row2['job_type'] == "Fix or Replace" ||
					$row2['job_type'] == "240v Rebook" ||
					$row2['is_eo'] == 1 ||
					$row2['j_status'] == 'DHA' ||
					$row2['j_status'] == 'On Hold' ||
					$row2['urgent_job'] == 1
				){
					$isPriority = 1;
				}else{
					$isPriority = 0;
				}


				if( $showRow ==1 ){

					$jobs_count++;

					//BG COLOUR
					$bgcolor = "#FFFFFF";
					if($row2['job_reason_id']>0){
						$bgcolor = "#fffca3";
					}else  if($row2['ts_completed']==1){
						$bgcolor = "#c2ffa7";
						$comp_count++;
					}

					$j_created = date("Y-m-d",strtotime($row2['created']));

					// unsorted, bright yellow
					/*
					if( $row['dnd_sorted']==0 ){
						$bgcolor = '#FFFF00';
					}
					*/

					// color row pink if precomp jobs was moved to booked and is techsheet complete
					if( $row['precomp_jobs_moved_to_booked']==1 ){
						$bgcolor = 'pink';
					}


					// check for not complete reason
					$jnc_sql = $this->db->query("
						SELECT COUNT(`jobs_not_completed_id`) AS jnc_count
						FROM `jobs_not_completed`
						WHERE `job_id` = {$row2['jid']}
					");
					$jnc_count = $jnc_sql->row()->jnc_count;

					if( $jnc_count > 0 ){
						$bgcolor = 'orange';
					}

					// if job type is 'IC Upgrade' show IC upgrade icon
					$show_ic_icon = ( $row2['job_type'] == 'IC Upgrade' )?1:0;


					switch($row2['j_status']){
						case 'Merged Certificates':
							$jstatus_txt = 'Merged';
						break;
						case 'Pre Completion':
							$jstatus_txt = 'Pre Comp';
						break;
						default:
							$jstatus_txt = $row2['j_status'];
					}



					// job row data
					// job status

					$old_crm_page = "view_job_details_tech.php";
					$old_crm_params = "id:{$row2['jid']}";
					//$old_ts_link  = $this->system_model->old_crm_redirect($old_crm_page,$old_crm_params)."&tr_id={$tr_id}";
					$old_ts_link = "/jobs/tech_sheet/?job_id={$row2['jid']}&tr_id={$tr_id}"; // update to new CI link

					//$status_td = "<button type='button' data-job_id='{$row2['jid']}' data-old_ts_link='{$old_ts_link}' class='btn process_btn'>Process</button>";
                    $status_td = "
                    <div class='checkbox'>
                        <input type='checkbox' id='job_id_chk_{$row2['jid']}' class='job_id_chk' value='{$row2['jid']}' />
                        <label for='job_id_chk_{$row2['jid']}'>&nbsp;</label>
                    </div>
                    ";


					// service
					$job_icons_params = array(
						'job_id' => $row2['jid'],						
						'display_in_email' => true
					);
					$service_td = $this->system_model->display_job_icons_v2($job_icons_params);


					// details
					$details_td = null;
					// if first visit
					if( $this->tech_model->check_prop_first_visit($row2['property_id']) == true   ){
						$fv = '<img src="'.$this->config->item('crmci_link').'/images/first_icon.png" class="row_icons" title="First visit" data-toggle="tooltip" /> ';
					}else{
						$fv = '';
					}
					$details_td .= $fv;


					//  if job type = COT, LR, FR, 240v or if marked Urgent
					if(
						$row2['job_type'] == "Change of Tenancy" ||
						$row2['job_type'] == "Lease Renewal" ||
						$row2['job_type'] == "Fix or Replace" ||
						$row2['job_type'] == "240v Rebook" ||
						$row2['is_eo'] == 1 ||
						$row2['urgent_job'] == 1
					){
						$details_td .= '<img src="'.$this->config->item('crmci_link').'/images/caution.png" class="row_icons" title="Priority Job" data-toggle="tooltip"/> ';
					}

					if( $row2['key_access_required'] == 1 && $row2['j_status']=='Booked' ){
						$details_td .= '<img src="'.$this->config->item('crmci_link').'/images/key_icon.png" class="row_icons" title="Key Access Required" data-toggle="tooltip" /> ';
					}


					$job_log_params = array(
						'sel_query' => "eventdate, eventtime",
						'job_id' => $row2['jid'],
						'eventdate' => date('Y-m-d'),
						'contact_type' => 'Phone Call'
					);
					$chk_logs_sql = $this->tech_model->getJobLogByJobId($job_log_params);
					$chk_log = $chk_logs_sql->row_array();

					$current_time = date("Y-m-d H:i:s");
					$job_log_time = date("Y-m-d H:i",strtotime("{$chk_log['eventdate']} {$chk_log['eventtime']}:00"));
					$last4hours = date("Y-m-d H:i",strtotime("-4 hours"));

					if(
						$row2['j_status']=='To Be Booked' && $chk_logs_sql->num_rows()>0 &&
						( $job_log_time >= $last4hours && $job_log_time <= $current_time )
					){
						$details_td .=  '<img src="'.$this->config->item('crmci_link').'/images/green_phone.png" class="row_icons" title="Phone Call" /> ';
					}

					if( $row2['p_state'] == 'NSW' && $row2['service_garage'] == 1 ){
						$details_td .= '<img src="/images/serv_img/service_garage_icon.png" class="row_icons" data-toggle="tooltip" title="Service Garage" />';
					}


					// cavi/orca alarms
					/*if( $this->config->item('country') == 2 ){ // NZ
						$cavi_orca_td = $this->system_model->display_orca_or_cavi_alarms($row2['agency_id']);
					}else{
						$cavi_orca_td = null;
					}*/

					$cavi_orca_td = null;
					if( $this->config->item('country') == 1 ){ // preferred alarm, AU only 

						if( $row2['p_state'] == 'QLD' ){ // QLD only

							if( $row['preferred_alarm_id'] > 0 ){

								$num_qld_alarm_txt = ( $row['qld_new_leg_alarm_num'] > 0 )?" ({$row['qld_new_leg_alarm_num']})":null;
								$cavi_orca_td = "{$row2['pref_alarm_make']}{$num_qld_alarm_txt}";

							}

						}else{ // non-QLD

							$use_short_name = true;
							$cavi_orca_td = $this->system_model->display_free_emerald_or_paid_brooks($row2['agency_id'],$use_short_name);

						}
						

					}else if( $this->config->item('country') == 2 ){ // cavi/orca alarms, NZ only
						$cavi_orca_td = $this->system_model->display_orca_or_cavi_alarms($row2['agency_id']);
					}


					// ladder
					if( $row2['survey_ladder']!='' ){

						// 4ft was changed to 3ft. older data already 4ft so just change labels
						$survey_ladder = '';
						if($row2['survey_ladder']=='4FT'){
							$survey_ladder = '3FT';
						}else{
							$survey_ladder = $row2['survey_ladder'];
						}

						$survey_ladder_td = "
							<div class='left'><img src='{$this->config->item('crmci_link')}/images/ladder.png' class='ladder_icon row_icons' />({$survey_ladder})</div>
						";
					}else{
						$survey_ladder_td = null;
					}


					// property address
					$paddress =  $row2['p_address_1']." ".$row2['p_address_2'].", ".$row2['p_address_3'];
					$address_td = "<a href='{$this->config->item("crm_link")}/view_property_details.php?id={$row2['property_id']}'>{$paddress}</a>";

					// key
					$key_td = "<span class='key_num_span'>".( ( $row2['key_number'] != '' )?$row2['key_number']:'No Key' )."</span>";

					if( $row2['key_access_required'] == 1 ){
						$key_td .= " <img class='key_icon row_icons' src='{$this->config->item('crmci_link')}/images/key_icon.png' /> ";
						// if job is entry notice, show pdf link
						if( $row2['job_entry_notice']==1 && $is_email == false ){
							$en_link_orig = "{$this->config->item('crm_link')}/view_entry_notice_new.php?letterhead=1&i={$row2['jid']}&m=".md5($row2['agency_id'].$row2['jid']); // orig link
							$key_td .= "
								<a target='_blank' href='{$en_link_orig}'>
									<span class='fa fa-file-pdf-o pdf_icon'></span>
								</a>
							";
						}
						?>
					<?php
					}

					// notes
					$notes_td = $row2['tech_notes'];

					// agent
					$agency_address = "{$row2['a_address_1']} {$row2['a_address_2']} {$row2['a_address_3']} {$row2['a_postcode']}";
					$agent_td = "
						<a href='/agency/view_agency_details/{$row2['agency_id']}'>".str_replace('*do not use*','',$row2['agency_name'])."</a>
						<input type='hidden' class='agency_address_txt' name='agency_address_txt' value='{$agency_address} \n{$row2['a_phone']}' />
					";

					// job id
					$job_id_td = "<a href='{$this->config->item("crm_link")}/view_job_details.php?id={$row2['jid']}'>{$row2['jid']}</a>";


					// age
					$age_td =  $this->gherxlib->getAge($j_created);


					// row data
					$row_data_arr = array(
						'id_td' => $j,
						'tr_id' => $row['tech_run_rows_id'],
						'tr_bg_color' => $bgcolor,
						'td_class' => 'jstatus',

						'status_td' => $status_td,
						'service_td' => $service_td,
						'details_td' => $details_td,
						'cavi_orca_td' => $cavi_orca_td,
						'survey_ladder_td' => $survey_ladder_td,
						'address_td' => $address_td,
						'key_td' => $key_td,
						'notes_td' => $notes_td,
						'agent_td' => $agent_td,
						'age_td' => $age_td,
						'prop_id'=> $row['property_id'],
						'job_id_td' => $job_id_td
					);

					// job row view here
					$this->load->view('tech_run/available_dk_row_list_admin', $row_data_arr);

					$prop_address[] = array(
						'address' => "{$row2['p_address_1']} {$row2['p_address_2']} {$row2['p_address_3']} {$row2['p_state']} {$row2['p_postcode']}",
						'status' => $row2['j_status'],
						'created' => date("Y-m-d",strtotime($row2['created'])),
						'urgent_job' => $row2['urgent_job'],
						'highlight_color' => $row['highlight_color'],
						'lat' => $row2['p_lat'],
						'lng' => $row2['p_lng'],
						'trr_id' => $row['tech_run_rows_id']
					);

					$j++;

				}

			// ROW IS KEYS
			}

		?>

	<?php


	}
	?>


</tbody>

</table>

<div id="process_div" class="float-right">
    <button type="button" class="btn" id="process_as_complete_btn">Assign Door Knocks</button>
</div>

<!--  THIS IS FOR COMPLETED COUNT AND JOB COUNT START -->
<input type="hidden" id="jobs_count" value="<?php echo $jobs_count; ?>" />
<input type="hidden" id="comp_count" value="<?php echo $comp_count; ?>" />
<!--  THIS IS FOR COMPLETED COUNT AND JOB COUNT END -->



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
<a href="javascript:void(0);" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
	<p>
		This page displays the technicians schedule for the given day
	</p>
</div>

<!-- pick up -->
<a href="javascript:void(0);" id="process_fb_trigger" class="fb_trigger" data-fancybox data-src="#process_fb">Trigger the fancybox</a>
<div id="process_fb" class="fancybox process_fb_div" style="display:none;" >

	<h2>Process</h2>

	<div class="row">

		<div class="col-md-6">
			<button type="button" class="btn btn-success float-left" id="complete_btn">Complete</button>
		</div>
		<div class="col-md-6">
			<button type='button' class='btn btn-danger float-right' id="utc_btn">Unable to complete</button>
		</div>

	</div>

	<div class="job_reason_div mt-3">
		<div class="row">

				<div class="col-md-6">
					<!-- job not complete reason  -->
					<select id="job_reason" class="form-control job_reason">
						<option value="">----</option>
						<?php
						foreach( $jr_sql->result() as $jr ){
						?>
							<option value="<?php echo $jr->job_reason_id; ?>"><?php echo $jr->name; ?></option>
						<?php
						}
						?>
					</select>
				</div>

				<!-- comment -->
				<div class="col-md-6">
					<input type="text" id="reason_comment" class="form-control reason_comment" placeholder="Comment" />
				</div>

		</div>

		<div class="row mt-3">
			<div class="col-md-12">
				<button type="button" class="btn float-right" id="utc_submit_btn">Submit</button>
			</div>
		</div>
	</div>

	<input type="hidden" id="job_id" />
	<input type="hidden" id="old_ts_link" />

</div>

<!-- Fancybox END -->

<?php
/*
echo "<pre>";
print_r($prop_address);
echo "</pre>";
*/
?>

<script type="text/javascript">

// display marker radius
function display_marker_radius(position,shadow_color,custom_text){

	if( shadow_color > 0 ){

		switch(parseInt(shadow_color)){
			// Blue
			case 1:
				var radius_color = '#00AEEF';
			break;
			// Green
			case 2:
				var radius_color = '#00ae4d';
			break;
			// Orange
			case 3:
				var radius_color = '#f15a22';
			break;
			// Pink
			case 4:
				var radius_color = '#9c163e';
			break;
			// Purple
			case 5:
				var radius_color = '#9b30ff';
			break;
			// Yellow
			case 6:
				var radius_color = '#FFFF00';
			break;
		}

		// Add the circle for this city to the map.
		var cityCircle = new google.maps.Circle({
			strokeColor: radius_color,
			strokeOpacity: 0.8,
			strokeWeight: 2,
			fillColor: radius_color,
			fillOpacity: 0.20,
			map: map,
			center: position,
			radius: 500
		});

	}

}

// get marker icon
function get_marker_icon(image){

	// custom icon
	var icon = {
		url: image,
		labelOrigin: new google.maps.Point(20,16)
	};

	return icon;
}

// add markers
function add_marker(position,popupcontent,icon,trr_id,prop_index,hide_label=false,is_job){

	if( hide_label == false ){

		var pin_number = prop_index+1;
		var label_txt = pin_number.toString(); // convert to string

		var label_options = {
			text: label_txt,
			fontWeight: "bold",
			color: 'black',
			fontSize: '12px'
		};

	}

	// add marker
	var beachMarker = new google.maps.Marker({
		position: position,
		map: map,
		icon: icon,
		label: label_options
	});


	marker_data = {
		marker:beachMarker,
		address:popupcontent,
		coordinates:position,
		trr_id:trr_id,
		orig_icon:icon,
		is_job: is_job
	}
	markersArray.push(marker_data);

	// pop up window
	jAddPopUpWindow(beachMarker,popupcontent);


}


// pop up window
function jAddPopUpWindow(beachMarker,contentString){

	var infowindow = new google.maps.InfoWindow({
		content: contentString
	});

	google.maps.event.addListener(beachMarker, 'click', function() {
		infowindow.open(map,beachMarker);
	});

}


// distance
function calculateDistances(start,destination,row) {

	var service = new google.maps.DistanceMatrixService();
	service.getDistanceMatrix(
	{
		origins: [start],
		destinations: [destination],
		travelMode: google.maps.TravelMode.DRIVING,
		unitSystem: google.maps.UnitSystem.METRIC,
		avoidHighways: false,
	avoidTolls: false
	}, function(response, status){
		distance_callback(response,status,row)
	});

}

function distance_callback(response, status,row) {

	var jtext = "";

	if (status != google.maps.DistanceMatrixStatus.OK) {

		alert('Error was: ' + status);

	}else{

		var origins = response.originAddresses;
		var destinations = response.destinationAddresses;

		for (var i = 0; i < origins.length; i++) {
			var results = response.rows[i].elements;

			for (var j = 0; j < results.length; j++) {


				jtext = ' From: '+origins[i] + ' - To: ' + destinations[j]
				+ ' | Distance: ' + results[j].distance.text + ' | Duration: '
				+ results[j].duration.text + ' - Distance value : '+results[j].duration.value+'\n';
				//console.log(jtext);

				row.find(".time").html(results[j].duration.text);
				row.find(".distance").html(results[j].distance.text);

				tot_time += parseFloat(results[j].duration.text);
				tot_dis += parseFloat(results[j].distance.text);
				orig_dur += results[j].duration.value;

				var totalSec = orig_dur;
				var hours = parseInt( totalSec / 3600 ) % 24;
				var minutes = parseInt( totalSec / 60 ) % 60;
				var seconds = totalSec % 60;
				var time_str = "";
				if(hours==0){
					time_str = minutes+" mins";
				}else{
					time_str = hours+" hours "+minutes+" mins";
				}
				jQuery("#tot_time").html(time_str);
				//jQuery("#tot_time").html(tot_time+" mins");
				jQuery("#tot_dis").html(tot_dis.toFixed(1)+" km");

				address_index++;
			}
		}

	}

}

function deleteOverlays() {
	for (var i = 0; i < markersArray.length; i++) {
		markersArray[i].setMap(null);
	}
	markersArray = [];
}


function display_marker(address_lat_lng,address_obj,prop_index){

	var jdate = new Date(address_obj['created']);
	var last_60_day = new Date('<?php echo date("Y-m-d",strtotime("-60 days")); ?>');

	var hide_marker_label = false;
	var is_job = false;

	if( parseInt(address_obj['is_accomodation']) == 1 ){ // accomodation

		image = '<?php echo $this->config->item('crmci_link'); ?>/images/google_map/circle-pin-blue.png';
		hide_marker_label = true;

	}else if( parseInt(address_obj['is_keys']) == 1 || parseInt(address_obj['is_supplier']) == 1 ){ // keys and supplier

		image = '<?php echo $this->config->item('crmci_link'); ?>/images/google_map/circle-key-blue.png';
		hide_marker_label = true;

	}else{ // jobs

		if( ( address_obj['status'] == 'To Be Booked' && ( parseInt(address_obj['urgent_job']) == 1 || jdate < last_60_day ) ) || address_obj['status'] == 'Allocate' ){
			// image = '<?php echo $this->config->item('crmci_link'); ?>/images/google_map/pin-purple.png';
			image = '<?php echo $this->config->item('crmci_link'); ?>/images/google_map/pin-orange.png';
		}else if(address_obj['status'] == 'To Be Booked'){
			image = '<?php echo $this->config->item('crmci_link'); ?>/images/google_map/pin-orange.png';
		}else if(address_obj['status'] == 'Booked'){
			image = '<?php echo $this->config->item('crmci_link'); ?>/images/google_map/pin-green.png';
		}else if( address_obj['status'] == 'On Hold' || address_obj['status'] == 'On Hold - COVID' || address_obj['status'] == 'Escalate' ){
			//image = '<?php echo $this->config->item('crmci_link'); ?>/images/google_map/pin-black.png';
			image = '<?php echo $this->config->item('crmci_link'); ?>/images/google_map/pin-orange.png';
		}else{
			image = '<?php echo $this->config->item('crmci_link'); ?>/images/google_map/pin-green.png';
		}

		is_job = true;

	}

	var icon = get_marker_icon(image);

	// add markers
	add_marker(address_lat_lng,address_obj['address'],icon,address_obj['trr_id'],prop_index,hide_marker_label,is_job);

}

var directionsService;

function initGoogleAPI() {
	directionsService = new google.maps.DirectionsService();

	// generate map
	run_google_map();
}


// variables
var markersArray = [];
var map;
var distances = "";
var icon = '';
var image;
var jcount = 1;
var address_index = 1;

var tot_time = 0;
var tot_dis = 0;
var orig_dur = 0;

<?php
// convert PHP address array to js array
$js_array = json_encode($prop_address);
?>
var prop_address = <?php echo $js_array; ?>

var delayFactor = 0;
function generate_waypoints(wp_arr){

	var wp = [];

	if( wp_arr.length >= 2 ){

		// split array to start, end and waypoints
		for( let i = 0; i < wp_arr.length; i++ ){

			if( i == 0 ){ // start
				var start = wp_arr[i];
			}else if( i == (wp_arr.length-1) ){ // end
				var end = wp_arr[i];
			}else{
				wp.push({
					'location': wp_arr[i],
					'stopover':true
				});
			}

		}


		console.log("Start: ");
		console.log(start);
		console.log("Way points: ");
		console.log(wp);
		console.log("End: ");
		console.log(end);


		// instantiate direction object
		var directionsDisplay = new google.maps.DirectionsRenderer({
			'suppressMarkers': true
		});


		// direction options
		var request = {
			origin: start,
			destination: end,
			waypoints: wp,
			travelMode: google.maps.TravelMode.DRIVING,
			unitSystem: google.maps.UnitSystem.METRIC
		};

		// invoke direction
		directionsService.route(request, function(response, status) {

			if (status == google.maps.DirectionsStatus.OK) {

				directionsDisplay.setDirections(response);
				directionsDisplay.setMap(map);

			}else if (status === google.maps.DirectionsStatus.OVER_QUERY_LIMIT) {

				delayFactor++;
				setTimeout(function () {
					generate_waypoints(wp_arr);
				}, delayFactor * 1000);

			}

		});

	}

}

function run_google_map() {

	var center = new google.maps.LatLng(prop_address[0]['lat'], prop_address[0]['lng']);

	// instantiate map properties
	var mapOptions = {
		zoom: 13,  // zoom - 0 for maxed out out of earth
		center: center,
		gestureHandling: 'greedy'
	}

	// create the map
	map = new google.maps.Map(document.getElementById("map-canvas"), mapOptions);

	// loop through address
	var wp_limit = 25; // way point batch limit
	var wp_arr = [];
	for (var prop_index = 0; prop_index < prop_address.length; prop_index++) {

		var address_obj = prop_address[prop_index];
		var address_lat_lng = new google.maps.LatLng(address_obj['lat'], address_obj['lng']);

		display_marker(address_lat_lng,address_obj,prop_index); // display marker/pins

		// WAYPOINTS
		wp_arr.push(address_lat_lng); // add to waypoints stack

		if( wp_arr.length == wp_limit ){ // process per waypoint batch limit

			if( wp_arr.length >= 2  ){ // at least 2 address for start and end

				generate_waypoints(wp_arr); // generate way points

				wp_arr = []; // clear way points
				wp_arr.push(address_lat_lng); // store last waypoint as start on the next waypoint batch

			}

		}else if( prop_index == (prop_address.length-1) ){ // reach the last address

			if( wp_arr.length >= 2 ){ // at least 2 address for start and end

				generate_waypoints(wp_arr); // generate way points
				wp_arr = [];

			}

		}

	}


}


function toggle_process_as_complete_div(){

    if( jQuery(".job_id_chk:checked").length > 0 ){

        jQuery("#process_div").show();

    }else{

        jQuery("#process_div").hide();

    }

}


jQuery(document).ready(function(){

	// process lightbox
	jQuery(".process_btn").click(function(){

		var node = jQuery(this);
		var job_id = node.attr('data-job_id');
		var old_ts_link = node.attr('data-old_ts_link');

		console.log("job_id: "+job_id);

		// clear
		jQuery(".process_fb_div #job_id").val('');

		// load job ID to lightbox
		jQuery(".process_fb_div #job_id").val(job_id);
		jQuery(".process_fb_div #old_ts_link").val(old_ts_link);

		// trigger lightbox
		jQuery("#process_fb_trigger").click();

	});

	// UTC toggle
	jQuery("#utc_btn").click(function(){

		var utc_btn = jQuery(this);
		var utc_btn_txt = utc_btn.text();
		var orig_btn_txt = 'Unable to complete';

		if( utc_btn_txt == orig_btn_txt ){
			utc_btn.html("Cancel");
			jQuery("#complete_btn").hide();
			jQuery(".job_reason_div").show()
		}else{
			utc_btn.html(orig_btn_txt);
			jQuery("#complete_btn").show();
			jQuery(".job_reason_div").hide()
		}

	});

	jQuery("#process_as_complete_btn").click(function(){

        // get job ID
		var job_id_arr = [];
        jQuery(".job_id_chk:checked:visible").each(function(){

            var job_id = jQuery(this).val();

            if( job_id > 0 ){
                job_id_arr.push(job_id);
            }

        });

        if( job_id_arr.length > 0 ){

            $('#load-screen').show();
			jQuery.ajax({
				type: "POST",
				url: "/tech_run/ajax_dk_complete_by_bulk",
				data: {
					job_id_arr: job_id_arr,
					tech_id: <?php echo $tech_id; ?>,
					date: '<?php echo $date; ?>',
				}
			}).done(function( ret ){

				jQuery('#load-screen').hide();
				//location.reload();
				window.location='/tech_run/run_sheet_admin/<?php echo $this->input->get_post('tr_id'); ?>'

			});

        }		


	});



	jQuery("#utc_submit_btn").click(function(){

		var complete_btn = jQuery(this);
		var parent_div = complete_btn.parents(".process_fb_div");

		var job_id = parent_div.find("#job_id").val();
		var job_reason = parent_div.find("#job_reason").val();
		var reason_comment = parent_div.find("#reason_comment").val();
		var error = "";

		if( job_reason == "" ){
			error += "Not Completed Reason is requrired\n";
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

			if( job_id > 0 ){

				swal({
					title: "Warning!",
					text: "This job will mark job as uncompleted. Do you want to continue?",
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
							url: "/tech_run/ajax_dk_utc",
							data: {
								job_id: job_id,
								tech_id: <?php echo $tech_id; ?>,
								job_reason: job_reason,
								reason_comment: reason_comment
							}
						}).done(function( ret ){

							jQuery('#load-screen').hide();
							swal({
								title: "Success!",
								text: "Job marked as not completed",
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

    // check all
    jQuery("#job_id_chk_all").change(function(){

        var dom = jQuery(this);
        var is_ticked = dom.prop("checked");

        if( is_ticked == true ){

            jQuery(".job_id_chk").prop("checked",true);

        }else{

            jQuery(".job_id_chk").prop("checked",false);            
            
        }

        toggle_process_as_complete_div();

    });

    // single tick
    jQuery(".job_id_chk").change(function(){            

        toggle_process_as_complete_div();

    });


});
</script>
