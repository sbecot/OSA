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
 *      Merge in a single file all .css used by the application to
 * 		reduce number of requests a loading time
 *--------------------------------------------------------
 * History     :
 * 1.0.0 - 2012-10-01 : Release of the file
*/
header("Content-type: text/css");
require_once '../include/Mobile_Detect.php';

$dir    = '.';
$files = scandir($dir);

foreach ($files as 	&$file){
	if (preg_match("/.*\.css/", $file) && $file!="checkbox-radio.css"){
		require_once $file;
	}
}
$detect = new Mobile_Detect();
if (!$detect->isMobile()) {
	require_once "checkbox-radio.css";
}

?>
