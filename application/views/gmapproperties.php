<style>
 #map {
  width: 100% !important;
  height: 900px !important;  
  margin: 0 !important;
  padding: 0 !important;
} 
#search_address_tbody{
  display: none;
}
</style>
<div class="contents">
<div class="searcharea">
  <form method="post" id="jform" action="gmapproperties"> 
    <table class="table">

    
    
    <tr>  
      <td>Data For Month:
          <select id="data_for_month" name="tablesname" class="form-control">              
            <?php foreach ($tableres as   $value) {  ?>
              <option value="<?php echo $value->crontable ?>" <?php if ($value->crontable==$tablesname){ echo 'selected="selected"';}?> > <?php echo $value->crontitle ?> </option>            
            <?php } ?>
          </select>
      </td>      
      <td>Total Properties: <span id="tot_prop"><?php echo $total; ?></span></td>
      <?php if($propertyid>0) { ?>
        <td>Total Techs: <div><?php echo $staffmarkerscount;?></div></td> 
      <?php } ?> 

      <!--
      <td><button type="submit" id="showall" name="showall" class="btn">Show All Properties</button></td> 
      <td><button type="button" id="show_search" name="show_search" class="btn">Show Search</button></td>  
      <td><button type="button" id="showtech" name="showtech" class="btn" onclick="showtechs()">Show Techs</button></td>
       -->            
              
     
    </tr>

    <?php if($propertyid>0) { ?>
      <tr>  
        <td>Red: 500 KM</td>
        <td>Blue: 250KM</td> 
        <td>Green: 100KM</td>
      </tr>
    <?php } ?>  


    <!--
    <tbody id="search_address_tbody"> 

      <tr>
        <td>Address</td>
        <td colspan="100%">
          <input type="text" name="fullAdd" id="fullAdd" class="addinput vw-pro-dtl-tnt short-fld pac-target-input form-control" style="width: 581px !important;" value="<?php echo $fullAdd;?>" placeholder="Enter a location" autocomplete="off">
        </td>
      </tr>

      <tr>
        <td><input type="text" name="address_1" id="address_1" class="form-control" value="<?php echo $address_1;?>" placeholder="No."></td>
        <td><input type="text" name="address_2" id="address_2" class="form-control"  value="<?php echo $address_2;?>" placeholder="Street Name"></td>
        <td><input type="text" name="address_3" id="address_3" class="form-control"  value="<?php echo $address_3;?>" placeholder="Suburb."></td>
        <td><input type="text" name="state" id="state" class="form-control"  value="<?php echo $state;?>" placeholder="State"></td>
        <td><input type="text" name="postcode" id="postcode" class="form-control"  value="<?php echo $postcode;?>" placeholder="PostCode"></td>           
        <td>                   
            <button type="submit" id="search" name="search" class="btn">Search</button>                                                   
        </td>
      </tr>

    </tbody>
    -->
       
    </table> 
    <input type="hidden" id="locality" value="Sunnybank">
	<input type="hidden" id="sublocality_level_1">


	 
  </form>

  
  <div id="map"></div>
  
</div>
</div>


<script>
    var markers;
</script>
<!--- SATS google map API key --->
<!--<script src="https://maps.googleapis.com/maps/api/js?libraries=places&key=AIzaSyAUHcKVPXD_kJQyPCC-bvTNEPsxC8LAUmA"></script>--->

<!--- Syd's google map API key --->
<script src="https://maps.googleapis.com/maps/api/js?libraries=places&key=AIzaSyB4xs6BkYkxk5e_7IBldReCfLQ7F1PtRLM"></script>

<script src="https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js"></script>

 
<script > <?php echo "var markers2 =  ".json_encode($markers)." ;";?>   </script>
<?php if(isset($staffmarkers)){?>
<script > <?php echo "var staffmarkers =  ".json_encode($staffmarkers)." ;";?>   </script>
 <?php } ?>

 <script>

$('#load-screen').show();

var is_searched=false; 
  var options = {
      zoom: 4,
      center: {
        lat: -33.865143,
        lng: 151.209900
      }
    }
    var map = new google.maps.Map(document.getElementById('map'), options);   
    var bounds = new google.maps.LatLngBounds(); 
    var markerCluster ;
    var gmarkers = [];
	var staffmarkersall = [];
	var mapmarkers = []; 
  var gmarkersstaff = [];
  function initMap() {
      // map options
   
      $('#load-screen').show();
      $('#inputbtns input').hide();
    
      //Add marker
      markers=markers2;
      // Loop through markers
      
      for (var i = 0; i < markers.length; i++) {
        gmarkers.push(addMarker(markers[i]));
          
      }
	 
	 
	 
      // Add a marker clusterer to manage the markers.
      if(is_searched)
      {
        for (var i = 0; i < gmarkers.length; i++) {
                removeMarker(gmarkers[i]);
                  
              }
              
      }
      function removeMarker(mark) {
         mark.setMap(null);  
      }
    
      <?php if($propertyid > 0) { ?>
      console.log('addMarkerStaff Here----');
      for (var i = 0; i < staffmarkers.length; i++) {
        gmarkers.push(addMarkerStaff(staffmarkers[i]));
        
      }
     
      
		var point = new google.maps.LatLng( parseFloat(markers[0].coords.lat), parseFloat( markers[0].coords.lng));
		var circle = new google.maps.Circle({
		  radius: 100*1000, 
		  center: point,
		  map: map,
		  fillColor: '#00FF00',
		  fillOpacity: 0.1,
		  strokeColor: '#00FF00',
		  strokeOpacity: 0.6
		}); 
		var circle = new google.maps.Circle({
		  radius: 250*1000, 
		  center: point,
		  map: map,
		  fillColor: '#0000FF',
		  fillOpacity: 0.1,
		  strokeColor: '#0000FF',
		  strokeOpacity: 0.6
		});

		var circle = new google.maps.Circle({
		  radius: 500*1000, 
		  center: point,
		  map: map,
		  fillColor: '#FF0000',
		  fillOpacity: 0.1,
		  strokeColor: '#FF0000',
		  strokeOpacity: 0.6
		});


    <?php }else{ ?>
    
    for (var i = 0; i < staffmarkers.length; i++) {
          gmarkersstaff.push(addMarkerStaff(staffmarkers[i]));
          
        }
    
    <?php } ?>
	 
	 
	
	 
    //Add MArker function
    function addMarker(props) {
      var marker = new google.maps.Marker({
        //position: props.coords,
        position: new google.maps.LatLng(props.coords.lat, props.coords.lng),  
        map: map,

      });
     bounds.extend(marker.position);    
    
        
      /* if(props.iconImage){
        marker.setIcon(props.iconImage);
      } */
     
      //Check content
      if (props.content) {
        var infoWindow = new google.maps.InfoWindow({
          content: '<h3><a href="<?php echo $this->config->item("crm_link"); ?>/view_property_details.php?id='+props.property_id+'" target="blank">'+props.address_1+' '+props.address_2+', '+props.address_3+'</a></h3>'+
            '<p><a href="/agency/view_agency_details/'+props.agency_id+'" target="blank">'+props.content+'</a></p>' + 
            '<p><a href="<?php echo $prodetail;?>/'+props.property_id+'" class="btn btn-info">Get Nearest Tech</a></p>' 
        });
        marker.addListener('click', function() {
          infoWindow.open(map, marker);
        });
      }
      return marker;
    }

    function addMarkerStaff(props) {
      var marker = new google.maps.Marker({
        //position: props.coords,
        position: new google.maps.LatLng(props.coords.lat, props.coords.lng),  
        map: map,
        icon: {
          url: "http://maps.google.com/mapfiles/ms/icons/blue-dot.png"
        }

      });
     //bounds.extend(marker.position);    
    
        
      /* if(props.iconImage){
        marker.setIcon(props.iconImage);
      } */
     
      //Check content
      if (props.content) {
        var infoWindow = new google.maps.InfoWindow({
          content: '<h3><a href="/users/view/'+props.StaffID+'" target="blank">'+props.content+'</a></h3><p>'+props.address_1+' '+props.address_2+' '+props.address_3+'</p>'  
             
        });
        marker.addListener('click', function() {
          infoWindow.open(map, marker);
        });
        <?php if($propertyid <= 0) { ?>
         marker.setVisible(false);
       <?php } ?>
      }
      return marker;
    }



    <?php if($propertyid<=0) { ?>

      markerCluster = new MarkerClusterer(map, gmarkers, {
      imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m'
      });
    
      markerCluster.repaint();
    <?php  }  ?>
      map.fitBounds(bounds);
      $('#load-screen').hide();
      $('#inputbtns input').show();



      showtechs('Show Techs');


  }


  google.maps.event.addDomListener(window, 'load', initMap);


  function searchproperty()
  {
    if($('#address_1').val() =='' && $('#address_1').val() && $('#address_1').val()=='' )
    {
      alert('You must enter atleast one of the  address value');
      return;
    }
    $.ajax({
      method: "POST",
      dataType: "json",
      url: "<?php echo base_url('index.php/gmapproperties/search')?>",
      data: { address_1: $('#address_1').val() , address_2: $('#address_2').val() ,address_3:$('#address_3').val() }
    })
      .done(function( msg ) {
           
          // deleteOverlays();
           // markerCluster = new MarkerClusterer(map, gmarkers);
            //console.log('gmarkers',gmarkers);
          console.log('markers',msg);
            
            //console.log('markers',markers);
            // Loop through markers
            for (var i = 0; i < gmarkers.length; i++) {
               removeMarker(gmarkers[i]);
                
            }
            function removeMarker(mark) {
               mark.setMap(null);  
            }
            
            markers = [];
            markers=msg.markers;
            gmarkers = [];


            for (var i = 0; i < markers.length; i++) {
              gmarkers.push(addMarker(markers[i]));
                
            }
            console.log('markers',markers);
            
            //Add MArker function
            function addMarker(props) {
              var marker = new google.maps.Marker({
                //position: props.coords,
                position: new google.maps.LatLng(props.coords.lat, props.coords.lng),  
                map: map,

              });
             bounds.extend(marker.position);    
            

                
              /* if(props.iconImage){
                marker.setIcon(props.iconImage);
              } */
             
              //Check content
              if (props.content) {
                var infoWindow = new google.maps.InfoWindow({
                  content: '<h3><a href="<?php echo $this->config->item("crm_link"); ?>/view_property_details.php?id='+props.property_id+'" target="blank">'+props.address_1+' '+props.address_2+', '+props.address_3+'</a></h3>'+
                    '<p><a href="/agency/view_agency_details/'+props.agency_id+'" target="blank">'+props.content+'<a/></p>' + 
                    '<p><a href="<?php echo $prodetail;?>/'+props.property_id+'" class="btn btn-info">Get Nearest Tech</a></p>' 
                });
                marker.addListener('click', function(){
                    infoWindow.open(map, marker);
                });
              }
              return marker;
            }
            markerCluster.setMap(null);
             markerCluster = new MarkerClusterer(map, gmarkers, {
              imagePath:'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m'
           });
            markerCluster.repaint();
            map.fitBounds(bounds);

      });
  }
  function deleteOverlays() {
  if (gmarkers) {
    for (i in gmarkers) {
      gmarkers[i].set_map(null);
    }
    gmarkers.length = 0;
  }
}

/*
function showtechs()
{
  let v=$('#showtech').val();
  console.log(v);
  if(v=='Show Techs')
  {
    $('#showtech').val('Hide Techs');
    for (var i = 0; i < staffmarkers.length; i++) {
        //gmarkersstaff.push(addMarkerStaff(staffmarkers[i]));
        gmarkersstaff[i].setVisible(true);
        
      }
     // map.fitBounds(bounds);
  }
  else
  {
    $('#showtech').val('Show Techs');
    for (var i = 0; i < staffmarkers.length; i++) {
        gmarkersstaff[i].setVisible(false);
      }
    //  map.fitBounds(bounds);
  }
}*/

function showtechs(v)
{
  //let v=$('#showtech').val();
  console.log(v);
  if(v=='Show Techs')
  {
    $('#showtech').val('Hide Techs');
    for (var i = 0; i < staffmarkers.length; i++) {
        //gmarkersstaff.push(addMarkerStaff(staffmarkers[i]));
        gmarkersstaff[i].setVisible(true);
        
      }
     // map.fitBounds(bounds);
  }
  else
  {
    $('#showtech').val('Show Techs');
    for (var i = 0; i < staffmarkers.length; i++) {
        gmarkersstaff[i].setVisible(false);
      }
    //  map.fitBounds(bounds);
  }
}
</script>
 
 
<script>
	
var placeSearch, autocomplete;

// google address prefill
var componentForm2 = {
  route: {
    'type': 'long_name',
    'field': 'address_2'
  },
  locality: {
    'type': 'long_name',
    'field': 'locality'
  },
  sublocality_level_1: {
    'type': 'long_name',
    'field': 'sublocality_level_1'
  },
  administrative_area_level_1: {
    'type': 'short_name',
    'field': 'state'
  },
  postal_code: {
    'type': 'short_name',
    'field': 'postcode'
  }
};
	
	
	function initAutocomplete() {
  // Create the autocomplete object, restricting the search to geographical
  // location types.

    var options = {
        types: ['geocode'],
        componentRestrictions: {
            country: 'au'
        }
    };

  autocomplete = new google.maps.places.Autocomplete(
     (document.getElementById('fullAdd')),
     options
      );

  // When the user selects an address from the dropdown, populate the address
  // fields in the form.
  autocomplete.addListener('place_changed', fillInAddress);
}

// [START region_fillform]
function fillInAddress() {
  // Get the place details from the autocomplete object.
  var place = autocomplete.getPlace();

  // test
   for (var i = 0; i < place.address_components.length; i++) {
    var addressType = place.address_components[i].types[0];
    if (componentForm2[addressType]) {

        var val = place.address_components[i][componentForm2[addressType].type];
        document.getElementById(componentForm2[addressType].field).value = val;

    }

  }

  // street name
  var ac = $("#fullAdd").val();
  var ac2 = ac.split(" ");
  var street_number = ac2[0];
  $("#address_1").val(street_number);

  // get suburb from locality or sublocality
  var sublocality_level_1 = $("#sublocality_level_1").val();
  var locality = $("#locality").val();

  var suburb = ( sublocality_level_1 != '' )?sublocality_level_1:locality;
  $("#address_3").val(suburb);

  // get suburb from google object 'vicinity'
  if( $("#address_3").val() == '' ){
    $("#address_3").val(place.vicinity);
  }


  console.log(place);
}
$(document).ready(function(){

  $('#load-screen').hide();

	initAutocomplete();

  // manual load map
  jQuery("#data_for_month").change(function(){

    /*
    $('#load-screen').show();

    initMap();

    showtechs('Show Techs');

    $('#load-screen').hide();
    */

    jQuery("#jform").submit();

  });


  // show search hide/show toggle
  jQuery("#show_search").click(function(){

    jQuery("#search_address_tbody").show();

      var btn_node = jQuery(this);
      var btn_txt = btn_node.text();
      var orig_btn_txt = 'Show Search';
      var toggle_div = jQuery("#search_address_tbody");
      
      toggle_button(btn_node,orig_btn_txt,toggle_div);

    });

})	
	</script>
