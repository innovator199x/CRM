<link rel="stylesheet" href="/inc/css/separate/vendor/select2.min.css">
<style>
.col-mdd-3{
    max-width:16%;
}

#calendar_edit_fb{
	text-align: left;	
}

#calendar_edit_table{
	margin-bottom: 5px;
}

#calendar_edit_table li{
	list-style-type: none;
}

.accomodation_dp_tr{
	visibility: hidden;
}

.nogreen{ background-color: transparent !important;}

.day-col{
    background-color: white;
    padding-left:10px;
}

.tech-tr > td {
  display: flex;
  align-items: center;
 }

.center-text{
    justify-content: center;
}

.state-dropdown{
    min-width: 150px;
}


.select2-container--arrow .select2-selection--multiple .select2-selection__choice, .select2-container--default .select2-selection--multiple .select2-selection__choice, .select2-container--white .select2-selection--multiple .select2-selection__choice{
    color: #fff;
    background: #919fa9;
    border: none;
    font-weight: 600;
    font-size: 1rem;
    padding: 0 2rem 0 .5rem;
    height: 26px;
    line-height: 26px;
    position: relative;
}
.select2-container--arrow .select2-results__option--highlighted[aria-selected], .select2-container--default .select2-results__option--highlighted[aria-selected], .select2-container--white .select2-results__option--highlighted[aria-selected]{
    color:#00a8ff;
}
.select2-container--arrow .select2-selection--multiple, .select2-container--default .select2-selection--multiple, .select2-container--white .select2-selection--multiple{
    border-color: #d8e2e7;
    min-height: 38px;
}
.select2-container--default .select2-selection--multiple .select2-selection__rendered{
    box-sizing: border-box;
    list-style: none;
    margin: 0;
    padding: 0 5px;
    width: 100%;
}
.select2-container--default.select2-container--focus .select2-selection--multiple{
    border-color:#c5d6de!important;
}
#btn_assign_to{margin-top:16px;}
.select2-selection__choice{
    margin-top:6px!important;
}

.borderless td, .borderless th {
    border: none;
}

.main-table{
    overflow-x: hidden;
}
</style>

<div class="box-typical box-typical-padding">

<?php
	// breadcrumbs template
	$bc_items = array(
		array(
			'title' => $title,
			'status' => 'active',
			'link' => "/bookings/view_schedule"
		)
	);
	$bc_data['bc_items'] = $bc_items;
	$this->load->view('templates/breadcrumbs', $bc_data);

?>

<header class="box-typical-header">
    <div class="box-typical box-typical-padding">

        <?php
            echo form_open('/bookings/view_schedule');
        ?>
        <div class="for-groups row">
            <div class="col-md-10 columns">
                <div class="row">

                    <!-- Booking Schedule  -->
                    <div class="col-mdd-3">
                        <label for="agency_select">Booking Schedule</label>

                        <select id="sel_num_days" name="sel_num_days"  class="form-control field_g2">
                        <?php
                            for( $i = 1; $i<=14; $i++ ){ ?>
                                <option value="<?php echo $i; ?>" <?php echo ($num_days == $i)?'selected="selected"':''; ?>><?php echo $i; ?> days</option>
                            <?php
                            }
						?>
                        </select>
                        <div class="mini_loader"></div>
                    </div>

                    <!-- Tech Name -->
                    <div class="col-mdd-3">
                        <label for="agency_select">Tech Name</label>

                        <select id="sel_assigned_tech" name="sel_assigned_tech"  class="form-control field_g2">
                            <option value="">ALL</option>
                            <?php foreach($tech_runs as $tech_run): ?>
                                <option value="<?php echo $tech_run->assigned_tech; ?>"
                                <?php echo ($this->input->get_post('sel_assigned_tech') == $tech_run->assigned_tech) ?
                                        'selected="selected"':''; ?>>

                                <?php echo $tech_run->FirstName . " " . substr($tech_run->LastName,0,1).'.'; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="mini_loader"></div>
                    </div>

                    <!-- Run Status -->
                    <div class="col-mdd-3">
                        <label for="agency_select">Run Status</label>

                        <select id="sel_run_status" name="sel_run_status"  class="form-control field_g2">
                            <option value="">ALL</option>
                            <?php foreach($run_status as $rs): ?>
                                <option value="<?php echo $rs['id']; ?>"
                                    <?php echo ($this->input->get_post('sel_run_status') == $rs['id']) ?
                                        'selected="selected"':''; ?>>

                                    <?php echo $rs['status']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="mini_loader"></div>
                    </div>

                    <!-- Run Dates -->
                    <div class="col-mdd-3">
                        <label for="agency_select">Run Date</label>

                        <select id="sel_run_date" name="sel_run_date"  class="form-control field_g2">
                            <option value="">ALL</option>
                                <?php foreach($run_dates as $run_date): ?>
                                    <option value="<?php echo $run_date->date; ?>"
                                        <?php echo ($this->input->get_post('sel_run_date') == $run_date->date) ?
                                        'selected="selected"':''; ?>>
                                        <?php echo $run_date->date; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <div class="mini_loader"></div>
                    </div>
                    
                    <!-- State -->
                    <div class="col-md-2 columns" style="margin-left:-4px;">
                        <label>State</label>
                        <select id="state_filter" name="state_filter[]" class="select2 ttmoselect form-control" style="min-width:150px !important;" multiple="multiple" id="ttnimo" name="ttnimo">
                            <?php 
                                $sel_states = $this->input->get_post('state_filter');

                                foreach($states as $state):
                                    if ($state):
                                ?>
                                 <option <?php echo in_array($state->state, $sel_states, false) ? "selected" : ""; ?>  value="<?php echo $state->state; ?>">
                                        <?php echo $state->state; ?>
                                    </option>
                            <?php 
                                    endif;
                                endforeach; 
                            ?>
						</select>           
                    </div>

                    <div class="col-md-2" style="margin-left:-18px;">
                        <label for="phrase_select">Phrase</label>
                        <input type="text" name="search_phrase" class="form-control" placeholder="ALL" value="<?php echo $this->input->get_post('search_phrase'); ?>" />
                    </div>

                    <!-- Search Button -->
                    <div class="col-md-1 columns" style="margin-left:-18px;">
                        <label class="col-sm-12 form-control-label">&nbsp;</label>
                        <button type="submit" class="btn btn-inline">Search</button>
                    </div>


                </div>
            </div>
        </div>

        <?php echo form_close(); ?>

    </div>
</header>

    <section>
		<div class="body-typical-body">
			<div class="table-responsive main-table" id="booking_sched_ajax_box">
				<table class="table">
					<thead>
						<tr>
							<th style="width:7%;padding-left:10px;">Day</th>
							<th style="width:8%;">Technician</th>
							<th style="width:14%;">Area</th>
							<th style="width:22%;">Run Status</th>
							<th style="width:15%;">Booking Staff</th>
                            <th style="width:7%;" class="text-center"><img  style="width: 30px;" src="/images/black_house.png" /></th>
							<th style="width:7%;" class="text-center">Completed</th>
							<th style="width:8%;" class="text-center">Booked</th>
							<th style="width:8%;" class="text-center">DK</th>
                            <!-- <th style="width:8%;" class="text-center">Billable</th> -->
						</tr>
					</thead>
					<tbody>

                        <?php
                            $CI =& get_instance();
                            $CI->load->model('/Booking_model');

                            $tr = null;
                            $sel_run_status = $this->input->get_post('sel_run_status');
                            $sel_run_date = $this->input->get_post('sel_run_date');
                            $sel_assigned_tech = $this->input->get_post('sel_assigned_tech');
                            $search_phrase = $this->input->get_post('search_phrase');
                            $state_filter = $this->input->get_post('state_filter');
                                    
                            if ($sel_assigned_tech || $sel_run_date || $sel_run_status ||  $state_filter || $search_phrase) {

                                $src_params = (object)[
                                    "run_status" => $sel_run_status,
                                    "state_filter" => $state_filter,
                                    "search_phrase" => $search_phrase,
                                    "date" =>  $sel_run_date ? $sel_run_date : "",
                                    "assigned_tech" => $sel_assigned_tech,
                                    "group_by_date" => true
                                ];
                                      
                                $tr = $CI->Booking_model->get_tech_runs($src_params);	

                                $num_days = count($tr);

                            } 
                            
                            $row = 0;

                            for ($x = 0; $x < $num_days; $x++):

                                $tot_bt = 0;
                                $tot_comp = 0;
                                $tot_bkd = 0;
                                $tot_dk = 0;
                                $tot_bil = 0;
                               
                        ?>
                        <tr>
                            
                            <!-- Day -->
                            <td class="day-col chops">
						        <?php
                                    $date =  $sel_run_date ? $sel_run_date : explode("-", date_after($x, 2));
                                    if (!empty($src_params)){ 
                                        echo date('l', strtotime($tr[$row]->date)) . "</br>" . date("d/m/Y", strtotime($tr[$row]->date));
                                    } else {
                                        echo date_after($x,3);
                                    }
                                ?>
                            </td>

                            <td colspan="11">
                                
                                <table class="borderless w-100">         
           
                                    <?php 

                                        $jdate = "{$date[2]}-{$date[1]}-{$date[0]}";

                                        $search_params = (object)[
                                                    "run_status" => $sel_run_status,
                                                    "state_filter" => $state_filter,
                                                    "search_phrase" => $search_phrase,
                                                    "date" =>  $sel_run_date ? $sel_run_date : ($tr[$row]->date ? $tr[$row]->date : $jdate),
                                                    "assigned_tech" => $sel_assigned_tech ? $sel_assigned_tech : ""
                                                ];
                                              
                                        $tech_runs = $CI->Booking_model->get_tech_runs($search_params);	

                                        $tech_count = 0;

                                        foreach($tech_runs as $tech_run):

                                            $params = (object)[
                                                "assigned_tech" => $sel_assigned_tech ? $sel_assigned_tech : $tech_run->assigned_tech,
                                                "date" => $search_params->date 
                                            ];
                                            
                                            // calendar
                                            $calendar = $CI->Booking_model->get_calendar($params);
                                            // completed
                                            $completed = $CI->Booking_model->get_completed($params);
                                            // booked
                                            $booked = $CI->Booking_model->get_booked($params);
                                            // door knock
                                            $door_knock = $CI->Booking_model->get_door_knock($params);
                                            // billable
                                            // $billable = $CI->Booking_model->get_billable($params);
                             

                                            $tr_color = "";
                                            $run_status_txt = '';

                                            if ($src_params->search && $sel_run_status){
                                                // white
                                                if($tech_run->run_set == 1 && $search_params->run_status == 1){
                                                    $tr_color = 'background-color:#bfbfbf !important;';
                                                    $run_status_txt = 'Needs to be Coloured';
                                                }

                                                // purple
                                                if($tech_run->run_coloured == 1){
                                                    $tr_color = 'background-color: #d0ace6 !important;';
                                                    $run_status_txt = 'Coloured - Please Review';
                                                }

                                                // no color but have icon
                                                if($tech_run->ready_to_book == 1 && $search_params->run_status == 2){
                                                    $tr_color = "";
                                                    $str_icon_color = 'tech_run_icon_green.png';
                                                    $run_status_txt = '1st Call Over';
                                                }else{
                                                    $str_icon_color = 'tech_run_icon.png';
                                                }

                                                // yellow
                                                if($tech_run->first_call_over_done == 1 && $search_params->run_status == 3){
                                                    $tr_color = 'background-color: #f7f799 !important;';
                                                    $run_status_txt = '1st Call Done - Please Review';
                                                }

                                                // no color
                                                if($tech_run->run_reviewed == 1 && $search_params->run_status == 4){
                                                    $tr_color = '';
                                                    $run_status_txt = '2nd Call Over';
                                                }


                                                // yellow - 2nd Call Over Done
                                                if($tech_run->finished_booking == 1 && $search_params->run_status == 5){
                                                    $tr_color = 'background-color: #f7f799 !important;';
                                                    $run_status_txt = '	2nd Call Done - Please Review';
                                                }

                                                // white - Additional Call Over
                                                if($tech_run->additional_call_over == 1 && $search_params->run_status == 6){
                                                    $tr_color = '';
                                                    $run_status_txt = 'Extra Call Over';
                                                }

                                                // yellow - Additional Call Over Done
                                                if($tech_run->additional_call_over_done == 1 && $search_params->run_status == 7){
                                                    $tr_color = 'background-color: #f7f799 !important;';
                                                    $run_status_txt = 'Extra Call Done - Please Review';
                                                }

                                                // orange - ready to map
                                                if($tech_run->ready_to_map == 1 && $search_params->run_status == 8){
                                                    $tr_color = 'background-color:#f2b968 !important;';
                                                    $run_status_txt = 'Run Ready to Map - Please Review';
                                                }

                                                // green - run mapped
                                                if($tech_run->run_complete == 1 && $search_params->run_status == 9){
                                                    $tr_color = 'background-color: #bde897 !important;';
                                                    $run_status_txt = 'Booked & Mapped';
                                                }
                                                
                                                // white morning call over
                                                if($tech_run->morning_call_over == 1 && $search_params->run_status == 11){
                                                    $tr_color = '';
                                                    $run_status_txt = 'Morning Call Over';
                                                }

                                                // light blue
                                                if($tech_run->no_more_jobs == 1 && $search_params->run_status == 10){
                                                    $tr_color = 'background-color: #97e4e8 !important;';
                                                    $run_status_txt = 'Booked & Mapped FULL';
                                                }
                                            } else {
                                            
                                                 // white
                                                 if($tech_run->run_set == 1){
                                                    $tr_color = 'background-color:#bfbfbf !important;';
                                                    $run_status_txt = 'Needs to be Coloured';
                                                }

                                                // purple
                                                if($tech_run->run_coloured == 1){
                                                    $tr_color = 'background-color: #d0ace6 !important;';
                                                    $run_status_txt = 'Coloured - Please Review';
                                                }

                                                // no color but have icon
                                                if($tech_run->ready_to_book == 1){
                                                    $tr_color = "";
                                                    $str_icon_color = 'tech_run_icon_green.png';
                                                    $run_status_txt = '1st Call Over';
                                                }else{
                                                    $str_icon_color = 'tech_run_icon.png';
                                                }

                                                // yellow
                                                if($tech_run->first_call_over_done == 1){
                                                    $tr_color = 'background-color: #F7f799 !important;';
                                                    $run_status_txt = '1st Call Done - Please Review';
                                                }

                                                // no color
                                                if($tech_run->run_reviewed == 1){
                                                    $tr_color = '';
                                                    $run_status_txt = '2nd Call Over';
                                                }


                                                // yellow - 2nd Call Over Done
                                                if($tech_run->finished_booking == 1){
                                                    $tr_color = 'background-color: #F7f799 !important;';
                                                    $run_status_txt = '	2nd Call Done - Please Review';
                                                }

                                                // white - Additional Call Over
                                                if($tech_run->additional_call_over == 1){
                                                    $tr_color = '';
                                                    $run_status_txt = 'Extra Call Over';
                                                }

                                                // yellow - Additional Call Over Done
                                                if($tech_run->additional_call_over_done == 1){
                                                    $tr_color = 'background-color: #F7f799 !important;';
                                                    $run_status_txt = 'Extra Call Done - Please Review';
                                                }

                                                // orange - ready to map
                                                if($tech_run->ready_to_map == 1){
                                                    $tr_color = 'background-color:#f2b968 !important;';
                                                    $run_status_txt = 'Run Ready to Map - Please Review';
                                                }

                                                // green - run mapped
                                                if($tech_run->run_complete == 1){
                                                    $tr_color = 'background-color: #Bde897 !important;';
                                                    $run_status_txt = 'Booked & Mapped';
                                                }
                                                if($tech_run->morning_call_over == 1){
                                                    $tr_color = '';
                                                    $run_status_txt = 'Morning Call Over';
                                                }

                                                // light blue
                                                if($tech_run->no_more_jobs == 1){
                                                    $tr_color = 'background-color: #97e4e8 !important;';
                                                    $run_status_txt = 'Booked & Mapped FULL';
                                                }
                                            }
                                    ?>
                                    
                                    <tr class="nogreen d-flex tech-tr" style="border:none !important; <?php echo $tr_color; ?>">
                                         <!-- Technician  -->
                                        <td class="col-1">
                                            <?php
                                                $crm_ci_page = "/tech_run/run_sheet_admin/{$tech_run->tech_run_id}";
                                            ?>
                                            <a href="<?php echo $this->config->item("crm_link") ?>/set_tech_run.php?tr_id=<?php echo $tech_run->tech_run_id; ?>" style="float: left;">
                                                <img src="/images/tech_run/<?php echo $str_icon_color; ?>" />
                                            </a> &nbsp;&nbsp;

                                            <a href="<?php echo $this->config->item("crmci_link") ?>/tech_run/set/?tr_id=<?php echo $tech_run->tech_run_id; ?>" style="float: left;">
                                                <img src="/images/tech_run/<?php echo $str_icon_color; ?>" />
                                            </a>

                                            <a href="<?php echo $crm_ci_page; ?>" target="_blank" style="float: left; margin-right: 4px;">
                                            <?php
                                                echo !empty($tech_run->FirstName) && !empty($tech_run->LastName) ?
                                                "{$tech_run->FirstName} ".substr($tech_run->LastName,0,1).'.' : "";
                                            ?>
                                            </a>
                         
                                            <?php
                                                $tech_count++;
                                            ?>
                                        </td>

                                        <!-- Area -->
                                        <td class="col-2">
                                            <a data-calendar_id="<?php echo $calendar->calendar_id; ?>" onclick="updateCalendarEntry(<?php echo $calendar->calendar_id; ?>, <?php echo $tech_run->StaffID; ?>)" data-auto-focus="false" href="javascript:;">
												<?php echo $calendar->region; ?>
											</a>
                                        </td>
                                        <!-- Run Status -->
                                        <td class="col-3">
                                            <?php echo $run_status_txt; ?>
                                        </td>
                                        <!-- Booking Staff -->
                                        <td class="col-2">
                                            <?php
                                                echo format_staff_name($calendar->FirstName, $calendar->LastName);
                                            ?>
                                        </td>
                                        <!-- Accomodations -->
                                        <td class="col-1 center-text">
                                            <?php
                                                if($calendar->accomodation == 1){
                                                    echo '<img src="/images/tech_run/green_house.png"/>';
                                                }else if($calendar->accomodation != "" && $calendar->accomodation == 0){
                                                    echo '<img src="/images/tech_run/red_house.png"/>';
                                                }else if($calendar->accomodation == 2){
                                                    echo '<img src="/images/tech_run/orange_house2.png" />';
                                                }
                                            ?>
                                        </td>
                                        <!-- Completed -->
                                        <td class="col-1 center-text">
                                            <?php echo $completed->jcount; ?>
                                        </td>
                                        <!-- Booked -->
                                        <td class="col-1 center-text">
                                            <?php
                                                if ($booked->jcount == 0){ ?>
                                                <img title="Booked" class="" style="width: 16px;" src="/images/green_check.png" />
                                            <?php
                                                } else {
                                                    echo $booked->jcount;
                                                }
                                            ?>
                                        </td>
                                        <!-- Door Knock -->
                                        <td class="col-1 center-text">
                                            <?php echo $door_knock->jcount > 0 ? $door_knock->jcount : ""; ?>
                                        </td>
                                        <!-- Billable -->
                                        <!-- <td class="col-1 center-text">
                                            <?php // echo $billable->jcount > 0 ? $billable->jcount : ""; ?>
                                        </td> -->

                                    </tr>
                                    <?php

                                        $tot_comp += $completed->jcount;
                                        $tot_bkd += $booked->jcount;
                                        $tot_dk += $door_knock->jcount;
                                        // $tot_bil += $billable->jcount;
                                        
                                    endforeach;

                                        if ( $tot_bt!=0 || $tot_comp !=0 || $tot_bkd!=0 || $tot_dk!=0 || $tot_bil!=0 ){
                                    ?>
                                    <tr class="d-flex">
                                        <td class="col-9"><?php echo $tech_count; ?> Technicians</td>
                                        <td class="col-1 text-center"><?php echo $tot_comp; ?> <div>(<?php echo floor($tot_comp/$tech_count); ?> Avg)</div></td>
                                        <td class="col-1 text-center"><?php echo $tot_bkd; ?> <div>(<?php echo floor($tot_bkd/$tech_count); ?> Avg)</div></td>
                                        <td class="col-1 text-center"><?php echo $tot_dk; ?> <div>(<?php echo floor($tot_dk/$tech_count); ?> Avg)</div></td>
                                        <!-- <td class="col-1 text-center"><?php echo $tot_bil; ?> <div>(<?php echo floor($tot_bil/$tech_count); ?> Avg)</div></td> -->
                                    </tr>
                                    <?php } ?>

                                </table>
                    
                            </td>
                        </tr>
                        <?php
                                $row++;
                            endfor;
                        ?>

                    </tbody>
                    <tfoot>
                        <tr>
                            <?php
                                if ($this->input->get_post('days')){
                                    $disp_btn_txt = 'Display 7 Days Only'; 
                                    $disp_btn_param = '';		
                                } else {
                                    $disp_btn_txt = 'Display Next 7 Days'; 
                                    $next_num_days = 7+$num_days;
                                    $disp_btn_param = $next_num_days;
                                }

                                $total_num_days = 14+$num_days;

                            ?>
                            <td colspan="12">
                                <div class="row">
                                    <div class='col-6' >
                                        <?php echo form_open('/bookings/view_schedule/' . $disp_btn_param, ['class' => 'pull-right', 'id' => 'previus-form']); ?>
                                            <input type="hidden" name="days" id="display-7-days" value="<?php echo $disp_btn_param; ?>">
                                            <button type="submit" class="btn btn-sm btn-primary"><?php echo $disp_btn_txt; ?></button>
                                        </form>
                                    </div>
                                    <div class='col-6 pull-left'>
                                        <?php echo form_open('/bookings/view_schedule/'. $total_num_days, ['class' => 'pull-left', 'id' => 'next-form']); ?>
                                            <input type="hidden" name="days" id="display-next-14" value="<?php echo $total_num_days; ?>">                 
                                            <button type="submit" class="btn btn-sm btn-primary"> Display Next 14 Days</button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
	</section>

</div>


<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>
<div id="about_page_fb" class="fancybox" style="display:none;">

    <h4><?php echo $title; ?></h4>
    <p>
        This page shows all the current tech runs, and their status.
    </p>

</div>
<!-- Fancybox END -->

<script type="text/javascript">

function updateCalendarEntry(calendar_id, staff_id){
    $.fancybox.open({
        src: '/calendar/add_calendar_entry_static?id='+calendar_id+'&staff_id='+staff_id,
        type: 'ajax'
    }); 
}

$(document).ready(function() {
    

    jQuery("#sel_num_days").change(function(){
        var bs_num = jQuery(this).val();
        jQuery.ajax({
            type: "POST",
            url: "/bookings/update_preferred_day",
            data: { 
                staff_id: <?php echo $tech_run->StaffID; ?>,
                bs_num: bs_num
            }
        }).done(function( ret ) {
            window.location.href=`/bookings/view_schedule/${bs_num}`;
        });	
    });

    jQuery("#previus-form").submit(function(e){
        e.preventDefault();
        var bs_num = jQuery('#display-7-days').val();
        if(bs_num == ''){
            bs_num = 7;
        }
        jQuery.ajax({
            type: "POST",
            url: "/bookings/update_preferred_day",
            data: { 
                staff_id: <?php echo $tech_run->StaffID; ?>,
                bs_num: bs_num
            }
        }).done(function( ret ) {
            window.location.href=`/bookings/view_schedule/${bs_num}`;
        });	
    });

    jQuery("#next-form").submit(function(e){
        e.preventDefault();
        var bs_num = jQuery('#display-next-14').val();
        jQuery.ajax({
            type: "POST",
            url: "/bookings/update_preferred_day",
            data: { 
                staff_id: <?php echo $tech_run->StaffID; ?>,
                bs_num: bs_num
            }
        }).done(function( ret ) {
            window.location.href=`/bookings/view_schedule/${bs_num}`;
        });	
    });
});

</script>
