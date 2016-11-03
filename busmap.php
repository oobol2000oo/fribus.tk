<?php
  include 'header.html';
?>

<!DOCTYPE html>
<html>
  <head>
    <title>Busmap</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="utf-8">
    <style>
      /* Always set the map height explicitly to define the size of the div
       * element that contains the map. */
      #map {
        height: 100%;
      }
      /* Optional: Makes the sample page fill the window. */
      html, body {
        height: 100%;
        margin: 0;
        padding: 0;
      }
    </style>
  </head>
  <body>
    <script type="text/javascript" src="//maps.googleapis.com/maps/api/js?libraries=places&key=AIzaSyCpqK0y2zfq5DoD_KLsoWn3Ec7-BjY0k80"></script>
    <script type="text/javascript">
      var map;
      var infowindow;
      var pos = new google.maps.LatLng(10.0590288, 105.7662794);
      var PlaceService;
      var DirectionsService = new google.maps.DirectionsService();
      var DirectionsRenderer = new google.maps.DirectionsRenderer();
      function initialize(){
        map = new google.maps.Map(document.getElementById('map'), {
          center: pos,
          zoom: 17
        });
        DirectionsRenderer.setMap(map);
        infoWindow = new google.maps.InfoWindow({map: map});
        /*Try HTML5 geolocation.*/
        if (navigator.geolocation) {
          navigator.geolocation.getCurrentPosition(
            function(position){
              /*Reset location via geolocation*/
              pos = {
                lat: position.coords.latitude,
                lng: position.coords.longitude
              };
              /*Set window above location*/
              infoWindow.setPosition(pos);
              infoWindow.setContent('Vị trí của bạn.');
              map.setCenter(pos);
              /*Search bus station*/
              var request = {
                location: pos,
                radius: 1000,
                types: ['bus_station']
              };
              PlaceService = new google.maps.places.PlacesService(map);
              PlaceService.nearbySearch(request, callback);
            }, function() {
              handleLocationError(true, infoWindow, map.getCenter());
            }
          );
        } else {
          /*Browser doesn't support Geolocation*/
          handleLocationError(false, infoWindow, map.getCenter());
        }
      }
      function callback(PlaceResult, PlacesServiceStatus) {
        if (PlacesServiceStatus == google.maps.places.PlacesServiceStatus.OK) {
          for (var i = 0; i < PlaceResult.length; ++i) {
            createMarker(PlaceResult[i]);
          }
          /*Create waypoint*/
          var request = {
            origin: pos,
            destination: PlaceResult[0].geometry.location,
            travelMode: google.maps.DirectionsTravelMode.WALKING
          }
          DirectionsService.route(request,
            function(DirectionsResult, DirectionsStatus) {
              if (DirectionsStatus ==  google.maps.DirectionsStatus.OK) {                
                DirectionsRenderer.setDirections(DirectionsResult);
                alert('Bến xe bus gần nhất cách bạn ' + DirectionsResult.routes[0].legs[0].distance.text);
              }
            }
          );
        } else alert('Không có bến xe bus nào gần bạn.');
      }
      /*Create maker*/
      function createMarker(place) {
        var placeLoc = place.geometry.location;
        var marker = new google.maps.Marker({
          map: map,
          position: place.geometry.location
        });
        var content='<strong style="font-size:1.2em">'+place.name+'</strong>'+
                    '<br/><strong>Latitude:</strong>'+placeLoc.lat()+
                    '<br/><strong>Longitude:</strong>'+placeLoc.lng()+
                    '<br/><strong>Type:</strong>'+place.types[0]+
                    '<br/><strong>Rating:</strong>'+(place.rating||'n/a');
        var more_content='<img src="http://googleio2009-map.googlecode.com/svn-history/r2/trunk/app/images/loading.gif"/>';
        /*make a request for further details*/
        PlaceService.getDetails({reference:place.reference}, function (place, status){
          if (status == google.maps.places.PlacesServiceStatus.OK){
            more_content='<hr/><strong><a href="'+place.url+'" target="details">Details</a>';
            if(place.website){
              more_content+='<br/><br/><strong><a href="'+place.website+'" target="details">'+place.website+'</a>';
            }
          }
        });
        google.maps.event.addListener(marker, 'click', function(){
          infowindow.setContent(content+more_content);
          infowindow.open(map, this);
        });
      }
      function handleLocationError(browserHasGeolocation, infoWindow, pos) {
        infoWindow.setPosition(pos);
        infoWindow.setContent(browserHasGeolocation ?
                              'Lỗi: Định vị thất bại.':
                              'Lỗi: Trình duyệt của bạn không hỗ trợ định vị.');
      }
      google.maps.event.addDomListener(window, 'load', function(){
        initialize();
      });
    </script>
    <div id="map"></div>
  </body>
</html>

<?php
  include 'footer.html';
?>