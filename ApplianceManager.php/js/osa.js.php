<?php
/*--------------------------------------------------------
 * Module Name : ApplianceManager
 * Version : 1.0.0
 *
 * Software Name : OpenServicesAccess
 * Version : 1.0
 *
 * Copyright (c) 2011 – 2014 Orange
 * This software is distributed under the Apache 2 license
 * <http://www.apache.org/licenses/LICENSE-2.0.html>
 *
 *--------------------------------------------------------
 * File Name   : ApplianceManager/ApplianceManager.php/js/osa.js.php
 *
 * Created     : 2012-02
 * Authors     : Benoit HERARD <benoit.herard(at)orange.com>
 *
 * Description :
 *      Merge in a single file all .js used by the application to
 * 		reduce number of requests a loading time
 *--------------------------------------------------------
 * History     :
 * 1.0.0 - 2012-10-01 : Release of the file
*/
require_once "../include/Localization.php";
//header("Content-type: text/javascript");

$dir    = '.';
$files = scandir($dir);

Localization::getString("app.title");  //force Load localization settings
$lastModify=0;
foreach ($files as 	&$file){
	if (preg_match("/.*\.js/", $file) ||preg_match("/.*\.php/", $file)){
		if (filemtime($file)>$lastModify){
			$lastModify=filemtime($file);
		}
	}
}
if (Localization::$lastModify>$lastModify){
	$lastModify=Localization::$lastModify;
}
$headers=getallheaders();
if (isset($headers['If-Modified-Since']) && (strtotime($headers['If-Modified-Since']) >=	 $lastModify)) {
	// Client's cache IS current, so we just respond '304 Not Modified'.
	header('Last-Modified: '.gmdate('D, d M Y H:i:s', $lastModify).' GMT', true, 304);
	die();
} else {
	// Image not cached or cache outdated, we respond '200 OK' and output the image.
	header('Last-Modified: '.gmdate('D, d M Y H:i:s', $lastModify).' GMT', true, 200);
	
}	



include "jquery-1.11.3.min.js";
echo "\n";
foreach ($files as 	&$file){
	if ((preg_match("/.*\.js/", $file)||preg_match("/.*\.js.php/", $file)) && $file!="jquery-1.8.2.js" && $file!="osa.js.php" ){
		include $file;
		echo "\n";
	}
}
if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/ApplianceManager/js/localization/datepicker-" . Localization::getString("locale") . ".js")){
	include "localization/datepicker-" . Localization::getString("locale") . ".js";
}
?>

