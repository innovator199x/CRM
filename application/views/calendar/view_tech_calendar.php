<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
<style>
    .quickLinksDivsss span.fa{font-size:18px;}
    .quickLinksDivsss span.fa-red{color:#b4151b;}
    .quickLinksDivsss span.fa-orange{color:#f15a22;}
    .quickLinksDivsss span.fa-green{color:#00ae4d;}
    .techcalendar th{
        height: 25px !important;
    }		
    .techcalendar td{
        height: 40px !important;
    }
    .fontColorLightGrey{
        /*color:red !important;*/
        color:#d3d3d3 !important;
    }
    table#cal-fluid td{
        text-align: center;
    }
    .joverflow_div {
        height: auto;
        overflow-x: scroll;
        width: auto;
    }
   
    .payroll_inputs{
       
    }
    #payrol_export_dates_div{
        float: right;
        display: none;
    }
    .techcalendar_table_first{width:120px;}
    .joverflow_div_first{
        float:left;
    }
    .vtc-hhh{
        display:block;
        clear:both;
    }
    tfoot tr th{
        background:#f6f8fa;
    }
    table.techcalendar tr.visible {
        display: table-row;
    }
    table.techcalendar tr {
        display: none;
    }
    .joverflow_div_first #cal-fluid td{
        height: 56px !important;
    }
    .joverflow_div #cal-fluid td {
        height: 56px !important;
    }
    .goyo_1{display:flex;margin-top:20px;margin-bottom:5px;}
    .goyo_chexboxes{display:block;}
    .goyo_chexbox{
        display: inline-block;
        margin-right:18px;
        cursor:pointer;
        margin-bottom:5px;
    }
    .staff_class_div{display:block;}
    .goyo_1 h2.heading{font-size:20px!important;font-weight:bold;margin:0!important;}
    .goyo_1 span.staff_check_all_span{padding-top:5px;margin-right:3px;}
    table.techcalendar td.weekend {
        background: #f6f8fa !important;
    }
    .joverflow_div #cal-fluid td div{
        /*width:125px;*/
        width:87px;
    }
    #cal-fluid tr th{
        text-align: center;
    }
</style>
<?php
  $export_links_params_arr = array(
	'month' => $month,
	'year' => $year
);
$export_link_params = '/calendar/staff_calendar_csv/?'.http_build_query($export_links_params_arr);
?>
<div class="box-typical box-typical-padding">

	<?php 
// breadcrumbs template
$bc_items = array(
    array(
        'title' => $title,
        'status' => 'active',
        'link' => "/calendar/view_tech_calendar"
    )
);
$bc_data['bc_items'] = $bc_items;
$this->load->view('templates/breadcrumbs', $bc_data);
?>

<header class="box-typical-header">

<div class="box-typical box-typical-padding">
        <div class="for-groupss row quickLinksDivsss">
            <div class="col-md-4 columns">
                <div style="float: left; margin-top: 5px;">
                    <div><h5 style="margin:0;">Accomodation</h5></div>
                    <div style='color:#b4151b; float:left'>Required(<span class="fa fa-red fa-home"></span>)</div> <div style="float:left; margin: 0 5px;">-</div> 
                    <div style='color:#f15a22; float:left'>Pending(<span class="fa fa-orange fa-home"></span>)</div> <div style="float:left; margin: 0 5px;">-</div> 
                    <div style='color:#00ae4d; float:left'>Booked(<span class="fa fa-green fa-home"></span>)</div>
                </div>
            </div>
            <div class="text-center col-md-4 columns">
                <div class="input-group mb-3" style="margin-top: 15px;">
                    <div class="input-group-prepend">
                        <?php echo '<a href="/calendar/view_tech_calendar/?month='. $backmonth .'&year='. $backyear .'" style="margin-top:10px; margin-right:10px;">&nbsp; <span class="fc-icon fc-icon-font-icon font-icon-arrow-left"></span> '.date("F",mktime(0,0,0,$backmonth,1,$backyear)).'</a>';?>
                    </div>
                    <input name="month_selection" placeholder="ALL" class="flatpickr-input form-control text-center" data-allow-input="true" id="month_selection" autocomplete="off" type="text" value="<?php echo date('F', mktime(0, 0, 0, $month, 10)) . ' ' . $year; ?>" readonly>
                    <div class="input-group-append">
                        <?php echo '<a href="/calendar/view_tech_calendar/?month='. $forwardmonth .'&year='. $forwardyear .'" style="margin-top:10px; margin-left: 10px;">'.date("F",mktime(0,0,0,$forwardmonth,1,$forwardyear)).'<span class="fc-icon fc-icon-font-icon font-icon-arrow-right"></span></a>';?>
                    </div>
                </div>
            </div>
            <div class="text-right col-md-4 columns">
                <section class="proj-page-section float-right" style="margin-left:20px;">
                    <div class="proj-page-attach" style="text-align:left;margin-top:0;">
                        <i class="fa fa-file-excel-o"></i>
                        <p class="name"><?php echo $title; ?></p>
                        <p>
                            <a href="<?php echo $export_link_params ?>">
                                Export
                            </a>
                        </p>
                    </div>
                </section>
                <section class="proj-page-section float-right" style="text-align:left;">
                    <div class="proj-page-attach" style="margin-top:0;">
                        <i class="fa fa-file-excel-o"></i>
                        <p class="name">Payroll</p>
                        <p>
                            <a data-auto-focus="false"  class="btn_payroll_export inline_fancybox" href="#payrolExportDevFancybox">
                                 Export
                            </a>
                        </p>
                    </div>

                     <div style="display:none;width:300px;" id="payrolExportDevFancybox">

                        <?php  
                         $form_attr = array('class' => 'payroll_export_form', 'id'=> 'payroll_export_form');
                         echo form_open('/calendar/staff_calendar_csv',$form_attr); ?>
                            <div class="form-group">
                                <label class="payroll_date_lbl">From:</label> 
                                <input  data-allow-input="true" type="text" name="payroll_from" class="form-control flatpickr flatpickr-input payroll_inputs" value="<?php echo date('d/m/Y',strtotime("{$year}-{$month}-1")) ?>" />
                            </div>
                            <div class="form-group">
                                <label class="payroll_date_lbl">To:</label> 
                                <input data-allow-input="true" type="text" name="payroll_to" class="form-control flatpickr flatpickr-input payroll_inputs" value="<?php echo date('t/m/Y',strtotime("{$year}-{$month}-1")) ?>" />
                            </div>
                            <div class="form-group">
                                <input type="hidden" name="payroll_export" value="1" />
                                <button class="btn" type="submit" id="btn_payroll_export_go" class="submitbtnImg vtc-exp">Export Payroll</button>
                            </div>
                        </form>
                     
                    </div>

                </section>
            </div>
        </div>


    <?php
        while($countday < $calendardays) {
                    
            $thedate = $countday + 1;
            $whiledate = $year.'-'.$month.'-'.$thedate;
            
            // check if date is weekend
            $weekDay = date('w', strtotime($whiledate));
            if($weekDay == 0 || $weekDay == 6) {
                $isWeekend = TRUE;
            } else {
                $isWeekend = FALSE;
            }
            
            $themonth[$countday]['date'] = $whiledate;
            $themonth[$countday]['weekend'] = $isWeekend;
                        
            $countday = $countday + 1;
        }
    ?>


    </div>

    
</header>

        <section>
            <div class="body-typical-body">
                
            <div class="vtc-hhh">

                <!-- FIRST TABLE LEFT START -->
                <div class="joverflow_div_first">
                    <table class="cal-fluid1 techcalendar techcalendar_table_first staff_name_tbl table main-table table_border" id="cal-fluid" cellpadding="0" cellspacing="0" border="0">
                        <thead>
                            <tr class="head visible text-center">
                                <th>Staff</th>
                            </tr>
                        </thead>				
                        <?php
                         $count_sort = 0;
                        foreach($query_tech->result_array() as $tech){ 
                            if( !in_array($tech['StaffID'], $staff_filter) && !in_array($tech['ClassID'], $staff_class_filter) ){ ?>
                            <tr class="date visible">
                                <?php
                                    $crm_ci_page = "/users/view/{$tech['StaffID']}";
                                ?>
                                <td class="staff_name_td"><a href="<?php echo $crm_ci_page; ?>" target="_blank"><?php echo $tech['FirstName'] .' '. (substr($tech['LastName'],0,1).'.'); ?></a></td>
                            </tr>
                            <?php
                            }
                            $count_sort++;
                        }
                        ?>
                        <tfoot>
                            <tr class="head visible text-center">
                                <th>Staff</th>
                            </tr>
                        </tfoot>				
                    </table>
                </div>
                <!-- FIRST TABLE LEFT END -->


                <!-- SECOND TABLE RIGH START -->
                <div class="joverflow_div" id="calendar_div">
                    <table style="margin-bottom:0;" class="cal-fluid2 techcalendar jcalendar table main-table table_border" id="cal-fluid" cellpadding="0" cellspacing="0" border="0">
                        
                    <?php
                    //The Table Header
                    echo '<thead>';
                    echo '<tr class="head visible" id="r_row_0">';
                        //Print out the name of each tech
                        foreach($themonth as $calday) {

                            #$thedate = date("d-m-Y", strtotime($calday[date]));
                            $thedate = date("jS", strtotime($calday[date]));
                            echo '<th>'. $thedate .'</th>';
                        }
                    
                    echo '</tr>';
                    echo '</thead>';
                    
                    echo '<tfoot>';
                    echo '<tr class="head visible" id="r_row_0">';
                        //echo '<th>Staff</th>';
                        //Print out the name of each tech
                        foreach($themonth as $calday) {

                            #$thedate = date("d-m-Y", strtotime($calday[date]));
                            $thedate = date("jS", strtotime($calday[date]));
                            echo '<th>'. $thedate .'</th>';
                        }
                    
                    echo '</tr>';
                    echo '</tfoot>';
                    
                    $count = 1;

                    foreach($query_tech->result_array() as $tech) {

                            $class = $count % 2 == 0 ? "row" : "row_alt";

                            if( !in_array($tech['StaffID'], $staff_filter) && !in_array($tech['ClassID'], $staff_class_filter) )
                            {
                                $class .= " visible";
                            }

                            echo '<tr class="date ' . $class . ' staff' . $tech['StaffID'] . '" id="r_row_' . $count . '">'; 
                            

                            $staff_params_aa = array(
                                'sel_query' => "*",
                                'staff_id' => $tech['StaffID']
                            );
                            $staff_sql = $this->gherxlib->getStaffInfo($staff_params_aa);

                            
                            $a = $staff_sql->row_array();
                            
                            $inner_count = 1;

                            foreach($themonth as $calday) {
                                
                                $inner_class = $inner_count % 2 == 0 ? "cell" : "cell_alt";
                                $inner_class .= $calday[weekend] == 1 ? " weekend" : "";
                                $today_txt = date("D",strtotime($calday['date']));
                                
                                $col_color = '';
                                
                                // highlight if today's date
                                if(date("Y-m-j")==$calday['date']){					
                                    $col_color = 'style="background-color:#DFFFA5 !important"';
                                }
                                
                                // day off highlight
                                if( strchr($tech['working_days'],$today_txt)==false && $calday[weekend]==0 ){
                                    $col_color = 'style="background-color:#ffcccb !important"';
                                }

                                echo '<td  '.$col_color.' class="' . $inner_class . ' clickable" rel="' . date('d-m-Y', strtotime($calday['date'])) . '_' . $tech['StaffID'] . '">';				 					
                                
                                // test echo
                                //echo $tech[StaffID].' - '.$calday['date'].' - '.$today_txt;
                                
                                if( strchr($tech['working_days'],$today_txt)==false && $calday[weekend]==0 ){
                                    echo "OFF";
                                }else{
                                    
                                    foreach($rows as $key=>$row) {
                                        
                                        
                                            
                                            $caldate = strtotime($calday['date']);
                                            $startdate = strtotime($row['date_start']);
                                            $finishdate = strtotime($row['date_finish']);
                                            if($caldate >= $startdate && $caldate <= $finishdate) {
                                                if($tech[StaffID] == $row[staff_id]) {
                                                    
                                                    // if leave on weekend, then hide it
                                                    $hide_str = '';
                                                    if( $row['marked_as_leave']==1 && $calday[weekend]==1 ){
                                                        $hide_str = 'display:none;';
                                                    }
                                                
                                                    $acc_color = "";
                                                
                                                    // accomodation color
                                                    if($row['accomodation']==1){ 															
                                                        $acc_color = 'green';																	
                                                    }else if($row['accomodation']==2){
                                                        $acc_color = 'orange';
                                                    }else if($row['accomodation']!="" && $row['accomodation']==0){
                                                        $acc_color = 'red';
                                                    }

                                                    if(strlen($row['region'])>10){
                                                        $cal_text = substr($row['region'],0,10)."...";
                                                    }else{
                                                        $cal_text = $row['region'];
                                                    }
                                                    
                                                    echo '<div '.(($row['marked_as_leave']==1 && $calday[weekend]!=1 )?'class="jhighlight"':'').' style="'.$hide_str.'">';
                                                    
                                                    echo '<a  data-fancybox="" data-type="ajax" style="color:'.$acc_color .'" rel="'. $row[calendar_id] .'" href="javascript:;" data-src="/calendar/add_calendar_entry_static?id='. $row[calendar_id] .'&staff_id='.$row[staff_id].'">'. $cal_text .'</a> '.(($row['booking_target']!=0 && $row['booking_target']!='')?$row['booking_target']:'');
                                                    
                                                    echo '</div>';

                                                    if($startdate == $finishdate)
                                                    {
                                                        // Delete if no longer needed - saves looping through again, speeds things up slighty :/
                                                        unset($rows[$key]);
                                                    }

                                                } else {
                                                }
                                            }
                                            
                                        
                                        
                                    }
                                    
                                }
                                
                                

                                $inner_count++;
                                
                                ?>
                                <script>
                                jQuery(".jhighlight").each(function(){
                                    jQuery(this).parents("td:first").attr('style','background-color: #ffcccb !important;'); 
                                });
                                </script>
                                <?php

                                echo '</td>';
                                
                            }
                            echo '</td>';
                        echo '</tr>';

                        $count++;
                    }
                    ?>
                    
                    </table>
                </div>
                <!-- SECOND TABLE RIGH END -->


            </div>

                <div style="margin-top:35px;margin-bottom:35px;">
                    <a class="btn hidden_fancybox_link" data-fancybox="" data-type="ajax" data-src="/calendar/add_calendar_entry_static" href="javascript:;">Add Event</a>
                    <a href="javascript:void(0)" class="btn btn-success" onclick="location.replace(location.pathname)">Refresh</a>
                </div>


                <?php 

                    $curr_group = "";

                            $newline_count = 1;
                            foreach($query_tech->result_array() as $index=>$tech)
                            {
                                if($tech['ClassName'] != $curr_group)
                                {
                                    if($curr_group != "")
                                    {
                                        // Close off old fieldset
                                        echo "</div>";
                                        $newline_count = 1;
                                    }
                                    
                                    $allowed_sc = !in_array($tech['ClassID'], $staff_class_filter);

                                    // Draw new legend
                                    echo "<div class='rowss staff_class_div'>";
                                    echo "<div class='goyo_1'>";
                                    echo "<h2 class='heading staff_class_header ".( ( $allowed_sc )?'':'fontColorLightGrey' )."' style='float: left;'>" . $tech['ClassName'] . "</h2>";
                                    echo "<input type='checkbox' style='float: left; margin: 0px 30px 0 11px;' class='staff_class_chk' ".( ( $allowed_sc )?'checked="checked"':'' )." value='{$tech['ClassID']}' />";
                                    echo '<div class="row staff_div" style="text-align: left;">
                                        <span class="staff_check_all_span fontColorLightGrey">Check All</span> <input type="checkbox" class="staff_check_all_chk" />
                                        </div></div>
                                        ';
                                    echo '<div style="clear:both;"></div>';

                                    $curr_group = $tech['ClassName'];

                                   
                                }
                              
                                
                                //if($allowed_sc){
                                  

                                echo "<label class='vtc-chckbx-h staff_label goyo_chexbox'><input type='checkbox' class='vtc-chckbx staff_chk'";

                                $allowed_staff = !in_array($tech['StaffID'], $staff_filter);
                                if( $allowed_staff )
                                {
                                    echo " checked ";
                                }

                                
                                    
                                    echo " value='" . $tech['StaffID'] . "' /> <span class='staff_span ".( ( $allowed_staff && $allowed_sc )?'':'fontColorLightGrey' )."'>" . $tech['FirstName'] . " " . (substr($tech['LastName'],0,1)) . ".</span></label>";

                                    $newline_count++;
                                    
                                //}		

                            }
                            
                            echo "</div>";

                    ?>
                <div style="margin-bottom:10px;">&nbsp;</div>

            </div>
          
        </section>

</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>							
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
	<p>
    This is the staff calendar used to track days worked, holidays, events and travel.
	</p>

</div>
<!-- Fancybox END -->

<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>
<script>


    function fakeScroll(elem,insertBefore){

    // get inner container width
    var container_width = elem.width();

    // fake scroll html format
    var scroll_format = ''+
        '<div class="cloned_top_scroll" style="overflow-x: scroll; overflow-y: hidden;">'+
            '<div class="inner_scroll" style="width: '+container_width+'px; padding-top: 1px;"></div>'+
        '</div>';

    // insert the fake scroll on top
    insertBefore.before(scroll_format);

    // sync boths scrolls
    jQuery(".cloned_top_scroll").scroll(function(){
        
        var scroll_left = jQuery(this).scrollLeft();
        jQuery("#calendar_div").scrollLeft(scroll_left);
        
    });

    jQuery("#calendar_div").scroll(function(){
        
        var scroll_left = jQuery(this).scrollLeft();
        jQuery(".cloned_top_scroll").scrollLeft(scroll_left);
        
    });

    }

    jQuery('#month_selection').flatpickr({
        plugins: [
            new monthSelectPlugin({
                shorthand: true, //defaults to false
                dateFormat: "F Y", //defaults to "F Y"
                altFormat: "F Y", //defaults to "F Y"
            })
        ]
	});

    jQuery("#month_selection").change(function(){
        let month_year = $(this).val();
        let month = getMonthFromString(month_year);
        month = (month <= 9 ? '0'+month : month);
        let year = month_year.split(" ");
        window.location='/calendar/view_tech_calendar/?month='+ month +'&year='+year[1];
    });

    function getMonthFromString(mon){
        var d = Date.parse(mon);
        if(!isNaN(d)){
            return new Date(d).getMonth() + 1;
        }
        return -1;
    }





    jQuery(".jhighlight").each(function(){
        jQuery(this).parents("td:first").attr('style','background-color: #ffcccb !important;'); 
    });

    function zebraTable()
    {
        $("table.techcalendar tr").removeClass("row").removeClass("row_alt");
        $("table#cal-fluid tr.visible:odd").addClass("row");
        $("table#cal-fluid tr.visible:even").addClass("row_alt");
        $("table#cal-fixed tr.visible:odd").addClass("row");
        $("table#cal-fixed tr.visible:even").addClass("row_alt");
    }


   $(document).ready(function() {

        $(".inline_fancybox").fancybox({
                'hideOnContentClick': true,
                'width': 500,
                'height': 'auto',
                'autoSize': false,
                'autoDimensions':false
            });
	
        jQuery("#load-screen").hide();
        
        
        
        // top scroll script
        //DoubleScroll(document.getElementById('calendar_div'));
        //jQuery(".joverflow_div_first").css('margin-top','18px');
        
        
        var elem_scroll = jQuery(".jcalendar");
        var insert_before = jQuery("#calendar_div");
        fakeScroll(elem_scroll,insert_before);
        var scroll_clone = jQuery(".cloned_top_scroll").clone();
        var staff_name_with = jQuery(".staff_name_td").width()
        scroll_clone.css('width',staff_name_with+'px');
        scroll_clone.css('visibility','hidden');
        jQuery(".staff_name_tbl").before(scroll_clone);
        //var fakeScroll_height = jQuery(".cloned_top_scroll").height();
        //jQuery(".joverflow_div_first").css('margin-top',fakeScroll_height);
        

        
        // check all class script
        jQuery(".staff_class_div").each(function(){

            var staff = jQuery(this).find(".staff_chk").length;
            var staff_checked = jQuery(this).find(".staff_chk:checked").length;
            
            //console.log("Staff: "+staff+" Checked Staff: "+staff_checked);
            
            if( staff_checked == staff ){
                jQuery(this).find(".staff_check_all_chk").prop("checked",true);
                //jQuery(this).find(".staff_check_all_span").removeClass("fontColorLightGrey");
            }

        });
        
        
        // check all/uncheck all toggle
        jQuery("#chk_staff_check_all").click(function(){
            if(jQuery(this).prop("checked")==true){
                jQuery(".vtc-chckbx").prop("checked",true);			
            }else{
                jQuery(".vtc-chckbx").prop("checked",false);
            }
            //window.location='/view_tech_calendar.php';
        });
        
        
        jQuery(".staff_check_all_chk").click(function(){
            
            if(jQuery(this).prop("checked")==true){
                jQuery(this).parents(".staff_class_div:first").find(".staff_chk").prop("checked",true);	
                jQuery(this).parents(".staff_class_div:first").find(".staff_span").removeClass("fontColorLightGrey");			
            }else{
                jQuery(this).parents(".staff_class_div:first").find(".staff_chk").prop("checked",false);
                jQuery(this).parents(".staff_class_div:first").find(".staff_span").addClass("fontColorLightGrey");			
            }
            
        });
        
        jQuery(".staff_chk").click(function(){
            
            if(jQuery(this).prop("checked")==true){
                jQuery(this).parents(".staff_label:first").find(".staff_span").removeClass("fontColorLightGrey");			
            }else{
                jQuery(this).parents(".staff_label:first").find(".staff_span").addClass("fontColorLightGrey");			
            }
            
        });
        
        // check all/uncheck all toggle
        jQuery("#chk_staff_class_check_all").click(function(){
            if(jQuery(this).prop("checked")==true){
                jQuery("#load-screen").show();
                $.ajax({
                    type: "POST",
                    data: "CheckAllStaffClass=1&check_all=1",
                    url: "ajax/ajax.php",
                    dataType: 'json',
                    cache:false,
                    success: function(response){			
                        jQuery("#load-screen").hide();
                    }
                });
                jQuery(".staff_class_chk").prop("checked",true);			
            }else{
                jQuery("#load-screen").show();
                $.ajax({
                    type: "POST",
                    data: "CheckAllStaffClass=1&check_all=0",
                    url: "ajax/ajax.php",
                    dataType: 'json',
                    cache:false,
                    success: function(response){
                        jQuery("#load-screen").hide();
                    }
                });
                jQuery(".staff_class_chk").prop("checked",false);
            }
            //window.location='/view_tech_calendar.php';
        });

        


        // staff filter
        $("input.staff_chk, .staff_check_all_chk").on("change", function(){
            
            var staff_id = $(this).val();

          

            // Re Zebra
            //zebraTable();

            var serialized = "";

            // Send unchecked boxes to db
            $("input.staff_chk:not(:checked)").each(function() {
                serialized += "," + $(this).val();
            });
            
            jQuery("#load-screen").show();
            $.ajax({
                type: "POST",
                data: "UpdateCalFilter=1&serialized=" + serialized,
                url: "/calendar/cal_ajax",
                dataType: 'json',
                cache:false,
                success: function(response){		
                    jQuery("#load-screen").hide();
                }
            });
        });
        
        
        // staff class filter
        $("input.staff_class_chk").on("change", function(){
            
            var staff_class_id = $(this).val();

            
            if($(this).is(":checked"))
            {
                //$("tr.staff" + staff_id).fadeIn().addClass("visible");
                jQuery(this).parents(".staff_class_div:first").find(".staff_class_header").removeClass("fontColorLightGrey");
            }
            else
            {
                //$("tr.staff" + staff_id).hide().removeClass("visible");
                jQuery(this).parents(".staff_class_div:first").find(".staff_class_header").addClass("fontColorLightGrey");
            }
            

            // Re Zebra
            //zebraTable();

            var sc_serialized = "";

            // Send unchecked boxes to db
            $("input.staff_class_chk:not(:checked)").each(function() {
                sc_serialized += "," + $(this).val();
            });
            
            jQuery("#load-screen").show();
            $.ajax({
                type: "POST",
                data: "UpdateCalStaffClassFilter=1&sc_serialized="+sc_serialized,
                url: "/calendar/cal_ajax",
                dataType: 'json',
                cache:false,
                success: function(response){
                    jQuery("#load-screen").hide();
                }
            });
        });
        
        

        

        // Make cells clickable
        $("td.clickable").on('dblclick', function() {
            //get date and staff ID
            var rel = $(this).attr("rel").split("_");

            var url = 'add_calendar_entry_static.php?startdate=' + rel[0] + '&staff_id=' + rel[1];

            $.fancybox.open({
                src  : "/calendar/add_calendar_entry_static?startdate="+rel[0]+"&staff_id="+rel[1],
                type : 'ajax',
                opts : {
                    afterShow : function( instance, current ) {
                        console.info( 'done!' );
                    }
                }
            });
            
        });

        // Intercept link click and open popup too
        $("td.clickable a").on('click', function() {

            /*var id = $(this).attr("rel");
            var url = 'add_calendar_entry_popup.php?id=' + id;
            */
           /* var url = jQuery(this).attr("href");
            newwindow=window.open(url,'name','height=600,width=460,scrollbars=yes');
            if (window.focus) {newwindow.focus()}

            return false;*/
        });


        //---open edit calendar fancybox > link from old crm
        var popup = <?php echo ($this->input->get_post('popup')) ? $this->input->get_post('popup') : 0 ?>;
        var calendar_id = <?php echo ($this->input->get_post('calendar_id')) ? $this->input->get_post('calendar_id') : 0 ?>;
        var staff_id = <?php echo ($this->input->get_post('staff_id')) ? $this->input->get_post('staff_id') : 0 ?>;

        if( popup==1 && calendar_id!=0){
            $.fancybox.open({
                src: '/calendar/add_calendar_entry_static?id='+calendar_id+'&staff_id='+staff_id,
                type: 'ajax'
            })
        }
        //---open edit calendar fancybox > link from old crm end

        
    });



</script>