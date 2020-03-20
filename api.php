<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
date_default_timezone_set("Europe/London");
$camerajson = json_decode(file_get_contents('https://api.tfl.gov.uk/Place/Type/JamCam'), true);
$signjson = json_decode(json_encode(simplexml_load_string(file_get_contents('https://data.tfl.gov.uk/tfl/syndication/feeds/vms_feed.xml'), "SimpleXMLElement", LIBXML_NOCDATA)),TRUE);
$cameras = [];
$signs = [];
foreach ($camerajson as $camera) {
	foreach ($camera["additionalProperties"] as $property) {
	  if ($property["category"] == "payload" && $property["key"] == "imageUrl") {
		$camera["imageurl"] = $property["value"];
		$camera['updated'] = $property["modified"];
	  }
	  if ($property["category"] == "payload" && $property["key"] == "videoUrl") {
		$camera["vidurl"] = $property["value"];
	  }
	}
	$cameras[] = array(						(strtotime($camera['updated']) < (time()) ? "0" : "1"), //whether it's out of date or not - aka 1 hour old
											$camera["commonName"],
											$camera["imageurl"],
											$camera["lat"],
											$camera["lon"],
											$camera["vidurl"]);
}
foreach ($signjson["Signs"]["Sign"] as $sign) {
	$signs[] = array(				 		$sign["siteid"],
											str_replace("|","\n",$sign["signtext"]),
											$sign["northing"],
											$sign["easting"],
											$sign["roadnr"]);
}
die(json_encode(["cameras" => $cameras, "signs" => $signs]));
?>
