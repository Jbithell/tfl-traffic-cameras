<?php
date_default_timezone_set("Europe/London");
$xml = simplexml_load_string(file_get_contents('https://s3-eu-west-1.amazonaws.com/tfl.pub/Jamcams/jamcams-camera-list.xml'));
$json = json_encode($xml);
$array = json_decode($json,TRUE)["cameraList"]["camera"];
$cameras = [];
for($x = 0; $x < count($array); $x++) {
	$data = $array[$x];
	if (!$data["@attributes"]["available"]) continue;
	$cameras[] = array(date("l jS \of F Y h:i:s A", strtotime($data["captureTime"])),
						$data["location"],
						'https://s3-eu-west-1.amazonaws.com/tfl.pub/Jamcams/' . $data["file"],
						$data["lat"],
						$data["lng"]);
}
?>
<html>
	<head>
	<title>Live Traffic Camera Map</title>
	<style type="text/css">
	  html { height: 100% }
	  body { height: 100%; margin: 0; padding: 0 }
	  #map_canvas { height: 100% }
	</style>
	<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB1tbIAqN0XqcgTR1-FxYoVTVq6Is6lD98&sensor=false"></script>
	<script type="text/javascript">
	var locations = [
	<?php
	for($x = 0; $x < count($cameras); $x++) {
		if ($x != 0) echo ',';
		echo "['" . $cameras[$x][1] . "', " . $cameras[$x][3] . ", " . $cameras[$x][4] . ", '" . '<img src="' . $cameras[$x][2] . '" alt="Camera Error" />' . "']";
	}
	?>
	  ];

	  function initialize() {

		var myOptions = {
		  center: new google.maps.LatLng(51.5081, 0.1281),
		  zoom: 12,
		  mapTypeId: google.maps.MapTypeId.ROADMAP

		};
		var map = new google.maps.Map(document.getElementById("default"),
			myOptions);

		setMarkers(map,locations)

	  }



	  function setMarkers(map,locations){

		  var marker, i

	for (i = 0; i < locations.length; i++)
	 {  

	 var location = locations[i][0]
	 var lat = locations[i][1]
	 var long = locations[i][2]
	 var textdetails =  locations[i][3]

	 latlngset = new google.maps.LatLng(lat, long);

	  var marker = new google.maps.Marker({  
			  map: map, title: location , position: latlngset
			});
			map.setCenter(marker.getPosition())


			var content = "<h1>" + location +  '</h1><br/>' + textdetails	 

	  var infowindow = new google.maps.InfoWindow()

	google.maps.event.addListener(marker,'click', (function(marker,content,infowindow){ 
			return function() {
			   infowindow.setContent(content);
			   infowindow.open(map,marker);
			};
		})(marker,content,infowindow)); 

	  }
	  }
	  </script>
</head>
<body onload="initialize()">
	<div id="default" style="width:100%; height:100%"></div>
</body>
</html>