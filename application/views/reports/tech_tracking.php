<div class="box-typical box-typical-padding">

    <?php
// breadcrumbs template
$bc_items = array(
    array(
        'title' => 'Run Sheet',
        'link' => "/tech_run/run_sheet_admin/{$this->input->get_post('tr_id')}"
    ),
    array(
        'title' => $title,
        'status' => 'active',
        'link' => $uri
    )
);
$bc_data['bc_items'] = $bc_items;
$this->load->view('templates/breadcrumbs', $bc_data);

?>
    <!--
	<header class="box-typical-header">
        <div class="box-typical box-typical-padding">
            <?php
            $form_attr = array(
                'id' => 'jform'
            );
            echo form_open($uri,$form_attr);
            ?>
                <div class="for-groupss row">
                    <div class="col-lg-10 col-md-12 columns">
                        <div class="row">


                            <div class="col-mdd-3">
                                    <label for="date_select">Date:</label>
                                    <input name="date_filter" placeholder="ALL" class="flatpickr form-control flatpickr-input" data-allow-input="true" id="flatpickr" type="text"  value="<?php echo ( $this->input->get_post('date_filter')!= '' )?$this->input->get_post('date_filter'):null; ?>">
                            </div>

                            <div class="col-md-1 columns">
                                <label class="col-sm-12 form-control-label">&nbsp;</label>
                                <input class="btn" type="submit" name="btn_search" value="Search">
                            </div>

                        </div>
                    </div>
                </div>
            </form>
        </div>
    </header>
    -->


    <div id="map-canvas" style="width:100%;height:500px;border:1px solid #cccccc;"></div>





    </div>

</div>

<!-- Fancybox Start -->
<a href="javascript:;" id="about_page_fb_link" class="fb_trigger" data-fancybox data-src="#about_page_fb">Trigger the fancybox</a>
<div id="about_page_fb" class="fancybox" style="display:none;" >

	<h4><?php echo $title; ?></h4>
	<p>Here is a list pin icons and its description: </p>

</div>
<!-- Fancybox END -->

<?php
/*
echo "<pre>";
print_r($tech_loc);
echo "</pre>";
*/
?>

<script>

    // display marker radius
    function display_marker_radius(position,shadow_color){

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

        if( prop_index > 0 && hide_label == false ){

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
               image = '<?php echo $this->config->item('crmci_link'); ?>/images/google_map/pin-purple.png';
           }else if(address_obj['status'] == 'To Be Booked'){
               image = '<?php echo $this->config->item('crmci_link'); ?>/images/google_map/pin-orange.png';
           }else if(address_obj['status'] == 'Booked'){
               image = '<?php echo $this->config->item('crmci_link'); ?>/images/google_map/pin-red.png';
           }else if( address_obj['status'] == 'On Hold' || address_obj['status'] == 'On Hold - COVID' || address_obj['status'] == 'Escalate' ){
               image = '<?php echo $this->config->item('crmci_link'); ?>/images/google_map/pin-black.png';
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

        // rectangular selection script
        rectangular_selection();
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
    $js_array = json_encode($tech_loc);
    ?>
    var tech_loc = <?php echo $js_array; ?>

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

        var center;
        if (tech_loc[0]) {
            center = new google.maps.LatLng(tech_loc[0]['lat'], tech_loc[0]['lng']);
        }
        else {
            center = new google.maps.LatLng(-21.2709300, 149.0645800);
        }

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
        for (var prop_index = 0; prop_index < tech_loc.length; prop_index++) {

            var address_obj = tech_loc[prop_index];
            var address_lat_lng = new google.maps.LatLng(address_obj['lat'], address_obj['lng']);

            // only display the last/most recent pin
            if( prop_index == (tech_loc.length-1) ){

                display_marker(address_lat_lng,address_obj,prop_index); // display marker/pins

                // add shadow radius
                if( address_obj['highlight_color'] != null ){
                    display_marker_radius(address_lat_lng,address_obj['highlight_color']);
                }

            }




            // WAYPOINTS
            wp_arr.push(address_lat_lng); // add to waypoints stack

            if( wp_arr.length == wp_limit ){ // process per waypoint batch limit

                if( wp_arr.length >= 2  ){ // at least 2 address for start and end

                    generate_waypoints(wp_arr); // generate way points

                    wp_arr = []; // clear way points
                    wp_arr.push(address_lat_lng); // store last waypoint as start on the next waypoint batch

                }

            }else if( prop_index == (tech_loc.length-1) ){ // reach the last address

                if( wp_arr.length >= 2 ){ // at least 2 address for start and end

                    generate_waypoints(wp_arr); // generate way points
                    wp_arr = [];

                }

            }


        }



    }


    function rectangular_selection(){


        // rectangular selection script
        var shiftPressed = false;

        $(window).keydown(function(evt) {
            if (evt.which === 16) { // shift
                shiftPressed = true;
            }
        }).keyup(function(evt) {
            if (evt.which === 16) { // shift
                shiftPressed = false;
            }
        });

        var mouseDownPos, gribBoundingBox = null,
        mouseIsDown = 0;
        var themap = map;


        google.maps.event.addListener(themap, 'mousemove', function(e) {
            if (mouseIsDown && shiftPressed) {
                if (gribBoundingBox !== null) // box exists
                {
                    bounds.extend(e.latLng);
                    gribBoundingBox.setBounds(bounds); // If this statement is enabled, I lose mouseUp events

                } else // create bounding box
                {
                    bounds = new google.maps.LatLngBounds();
                    bounds.extend(e.latLng);
                    var	sel_color_id = parseInt(jQuery("#row_highlight_color").val());
                    var	sel_color_txt = jQuery("#row_highlight_color option:selected").html().toLowerCase();
                    gribBoundingBox = new google.maps.Rectangle({
                    strokeColor: sel_color_txt,
                    fillColor: sel_color_txt,
                    map: themap,
                    bounds: bounds,
                    fillOpacity: 0.15,
                    strokeWeight: 0.9,
                    clickable: false
                    });
                }
            }
        });

        google.maps.event.addListener(themap, 'mousedown', function(e) {

            mouseIsDown = 1;
            mouseDownPos = e.latLng;

            if (shiftPressed) {
                themap.setOptions({
                    draggable: false
                });
            }

        });

        google.maps.event.addListener(themap, 'mouseup', function(e) {

            if (mouseIsDown && shiftPressed) {

                mouseIsDown = 0;

                if (gribBoundingBox !== null) // box exists
                {
                    var boundsSelectionArea = new google.maps.LatLngBounds(gribBoundingBox.getBounds().getSouthWest(), gribBoundingBox.getBounds().getNorthEast());

                    var selected_markers = [];
                    for (var key in markersArray) { // looping through my markersArray Collection


                        if (gribBoundingBox.getBounds().contains(markersArray[key].marker.getPosition())) {
                            //if(flashMovie !== null && flashMovie !== undefined) {
                            var	sel_color_id = parseInt(jQuery("#row_highlight_color").val());
                            var	sel_color_txt = jQuery("#row_highlight_color option:selected").html().toLowerCase();
                            var sel_pin;
                            if( sel_color_id != -1 ){

                                switch( sel_color_id ){

                                    case 1:
                                        sel_pin = '<?php echo $this->config->item('crmci_link'); ?>/images/google_map/sel-pin-blue.png';  // blue
                                    break;
                                    case 2:
                                        sel_pin = '<?php echo $this->config->item('crmci_link'); ?>/images/google_map/sel-pin-green.png';  // greem
                                    break;
                                    case 3:
                                        sel_pin = '<?php echo $this->config->item('crmci_link'); ?>/images/google_map/sel-pin-orange.png'; // orange
                                    break;
                                    case 4:
                                        sel_pin = '<?php echo $this->config->item('crmci_link'); ?>/images/google_map/sel-pin-pink.png'; //pink
                                    break;
                                    case 5:
                                        sel_pin = '<?php echo $this->config->item('crmci_link'); ?>/images/google_map/sel-pin-purple.png'; // purple
                                    break;
                                    case 6:
                                        sel_pin = '<?php echo $this->config->item('crmci_link'); ?>/images/google_map/sel-pin-yellow.png'; // yellow
                                    break;
                                }


                                var sel_icon = {
                                    url: sel_pin,
                                    labelOrigin: new google.maps.Point(20,16)
                                }

                            }else{
                                sel_icon = markersArray[key].orig_icon;
                            }

                            if( markersArray[key].is_job == true ){

                                markersArray[key].marker.setIcon(sel_icon);
                                selected_markers.push(markersArray[key].trr_id);

                            }


                        }

                    }

                    gribBoundingBox.setMap(null); // remove the rectangle

                    var	assign_color_mode = parseInt(jQuery("#assign_color_mode").val());

                    if( selected_markers.length>0 && assign_color_mode==1 ){

                        // assign color
                        jQuery("#load-screen").show();
                        jQuery.ajax({
                            type: "POST",
                            url: "/tech_run/ajax_assign_pin_colours/?tr_id=<?php echo $this->input->get_post('tr_id'); ?>&trr_id_arr="+selected_markers+"&trr_hl_color="+sel_color_id
                        }).done(function( ret ){
                            jQuery("#load-screen").hide();
                        });

                    }


                }
                gribBoundingBox = null;

            }

            themap.setOptions({
                draggable: true
            });

        });


    }



    jQuery(document).ready(function(){


        // get distance
        jQuery("#btn_display_distance").click(function(){

            address_index = 1;
            tot_time = 0;
            tot_dis = 0;
            orig_dur = 0;

            jQuery(".address").each(function(index){

                if(index>0){

                    var dom = jQuery(this);
                    var row = dom.parents("tr:first");
                    var orig = dom.parents("tr:first").prev('tr').find('.address').html();
                    var dist = dom.html();

                    setTimeout(function(){

                        // dunno how to pass variables on callback functions
                        calculateDistances(orig,dist,row);

                    }, 1000);

                }

            });

        });


        // assign colours
        jQuery("#btn_assign_colours").click(function(){

            var btn_txt = jQuery(this).text();
            var orig_btn_txt = 'Assign Colours';

            if( btn_txt == orig_btn_txt ){
                jQuery("#assign_color_mode").val(1); // enable color update
                jQuery(this).html("Cancel");
                jQuery("#btn_assign_color_div").show();
            }else{
                jQuery("#assign_color_mode").val(0); // disable color update
                jQuery(this).html(orig_btn_txt);
                jQuery("#btn_assign_color_div").hide();
            }


        });






        // clear all colors on map
        jQuery("#btn_clear_all_color").click(function(){

            swal({
                title: "Warning!",
                text: "This will clear all pin colours, continue?",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-success",
                confirmButtonText: "Yes, Continue",
                cancelButtonClass: "btn-danger",
                cancelButtonText: "No, Cancel!",
                closeOnConfirm: true,
                showLoaderOnConfirm: true,
                closeOnCancel: true
            },
            function(isConfirm) {

                if (isConfirm) {

                    jQuery("#load-screen").show();
                    jQuery.ajax({
                        type: "POST",
                        url: "/tech_run/ajax_clear_all_pin_colors/?tr_id=<?php echo $this->input->get_post('tr_id'); ?>"
                    }).done(function( ret ){

                        jQuery("#load-screen").hide();
                        location.reload();

                    });

                }

            });

        });


        // set start and end toggle
        jQuery("#btn_set_start_end").click(function(){

            var btn_txt = jQuery(this).text();
            var orig_btn_txt = 'Edit Start & End';

            if( btn_txt == orig_btn_txt ){
                jQuery(this).html("Cancel");
                jQuery("#start_end_main_div").show();
            }else{
                jQuery(this).html(orig_btn_txt);
                jQuery("#start_end_main_div").hide();
            }

        });

        // update start and end
        jQuery("#btn_update_map").click(function(){

            var start = jQuery("#start_point").val();
            var end = jQuery("#end_point").val();

            jQuery("#load-screen").show();
            jQuery.ajax({
                type: "POST",
                url: "/tech_run/ajax_update_start_and_end/?tr_id=<?php echo $this->input->get_post('tr_id'); ?>&start="+start+"&end="+end
            }).done(function( ret ){

                jQuery("#load-screen").hide();
                location.reload();

            });

        });

        // add keys
        jQuery("#btn_keys").click(function(){

            var btn_txt = jQuery(this).text();
            var orig_btn_txt = 'Add Keys';

            if( btn_txt == orig_btn_txt ){
                jQuery(this).html("Cancel");
                jQuery("#keys_div").show();
            }else{
                jQuery(this).html(orig_btn_txt);
                jQuery("#keys_div").hide();
            }

        });

        // keys
        jQuery("#btn_keys_submit").click(function(){

            var keys_agency = jQuery("#keys_agency").val();
            var error = "";

            if(keys_agency==""){
                error += "Agency is required";
            }

            if( error!="" ){
                alert(error);
            }else{

                jQuery("#load-screen").show();
                jQuery.ajax({
                    type: "GET",
                    url: "/tech_run/ajax_add_agency_keys/?tr_id=<?php echo $this->input->get_post('tr_id'); ?>&keys_agency="+keys_agency+"&tech_id=<?php echo $tech_id; ?>&date=<?php echo $date; ?>"
                }).done(function( ret ){

                    jQuery("#load-screen").hide();
                    location.reload();

                });


            }


        });


        // invoke table DND
        jQuery("#tbl_maps").tableDnD({

            onDrop: function(table, row) {

                var job_id = jQuery.tableDnD.serialize({
                    'serializeRegexp': null
                });

                //jQuery("#load-screen").show();
                jQuery.ajax({
                    type: "GET",
                    url: "/tech_run/ajax_sort_tech_run/?tr_id=<?php echo $this->input->get_post('tr_id'); ?>&"+job_id
                }).done(function( ret ){

                });

            }

        });


    });
</script>