<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="Cache-control" content="no-cache">
<meta http-equiv="Cache-control" content="no-store">
<title>Map Api for </title>
    <style type="text/css">
        html,
        body,
        #map {
          height: 94%;
          margin: 0;
          padding: 0;
        }
        .searcharea{ height: 60px; padding: 10px 20px; width: 100%;}
        table tr td { padding: 4px 8px }
        .contents { width: 100%; overflow: hidden; }

        input  select { padding: 2px 5px }

    </style>
<script type="text/javascript" src="<?php echo base_url('assets/jquery-3.6.0.min.js')?>"></script>

</head>

<body>
  <div class="contents">
<div class="searcharea">
  <form method="post" action="<?php echo base_url('index.php/gmapproperties')?>"> 
    <table border="0">
      <tr>
        <td>Enter   Address 1</td>
        <td>Enter  Address 2</td>
        <td>Enter   Address 3</td>
        <td>Data For Month</td>
        <td>&nbsp;</td>
        </tr>
        <tr>
          <td><input type="text" name="address_1" id="address_1" value="<?php echo $address_1;?>"></td>
          <td><input type="text" name="address_2" id="address_2"  value="<?php echo $address_2;?>"></td>
          <td><input type="text" name="address_3" id="address_3"  value="<?php echo $address_3;?>"></td>
          <td>
          <select name="tablesname">  
            <option value=""> Current Moth </option>  
            <?php foreach ($tableres as   $value) {  ?>
              <option value="<?php echo $value->crontable ?>" <?php if ($value->crontable==$selected){ echo 'selected="selected"';}?> > <?php echo $value->crontitle ?> </option>  
              // code...
            <?php } ?>
          </select>
        </td>
          <td>
              <input type="submit"  id="search" name="search" value="Search"   >
              <input type="submit" id="showall" name="showall" value="Show All Properties"  >
              <img src="<?php echo base_url('assets/loading.gif')?>" id="waitingico" style="display:none">
          </td>
        <td> Total Properties : <?php echo $total;?></td>
      </tr>
       
    </table> 
  </form>
</div>
</div>

<div id="map"> </div>
<script>
    var markers;
</script>
<script src="https://maps.googleapis.com/maps/api/js?libraries=places&key=AIzaSyB4xs6BkYkxk5e_7IBldReCfLQ7F1PtRLM"></script>
<script src="https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js"></script>

 
<script > <?php echo "var markers2 =  ".json_encode($markers)." ;";?>   </script>

 <script>

  var is_searched=false; 
  var options = {
      zoom: 4,
      center: {
        lat: 53.3938131,
        lng: -7.858913
      }
    }
    var map = new google.maps.Map(document.getElementById('map'), options);   
    var bounds = new google.maps.LatLngBounds(); 
    var markerCluster ;
    var gmarkers = [];
  function initMap() {
    // map options
   
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
    //Add marker
    markers=markers2;
    // Loop through markers
    
    for (var i = 0; i < markers.length; i++) {

      
      gmarkers.push(addMarker(markers[i]));
      //console.log('markers[i]',markers[i]);
    }
   
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
     console.log('props',props);
      //Check content
      if (props.content) {
        var infoWindow = new google.maps.InfoWindow({
          content: '<h3>'+props.content+'</h3><p>'+props.address_1+' '+props.address_2+' '+props.address_3+'</p>' + 
            '<p><a href="<?php echo $prodetail;?>/'+props.property_id+'" class="btn btn-info">Get Nearest Tech</a></p>' 
        });
        marker.addListener('click', function() {
          infoWindow.open(map, marker);
        });
      }
      return marker;
    }

      markerCluster = new MarkerClusterer(map, gmarkers, {
      imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m'
    });
      markerCluster.repaint();
      map.fitBounds(bounds);
  }


  google.maps.event.addDomListener(window, 'load', initMap)


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
               console.log('markers[i]',markers[i]);
                
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
                  content: '<h3>'+props.content+'</h3><p>'+props.address_1+' '+props.address_2+' '+props.address_3+'</p>' + 
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
</script>
 
</body>
</html>