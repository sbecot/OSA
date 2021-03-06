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
 * File Name   : ApplianceManager/ApplianceManager.php/objects/Error.class.php
 *
 * Created     : 2012-02
 * Authors     : Benoit HERARD <benoit.herard(at)orange.com>
 *
 * Description :
 *      .../...
 *--------------------------------------------------------
 * History     :
 * 1.0.0 - 2012-10-01 : Release of the file
*/

class Error{
	private $httpStatus;
	private $httpLabel;
	private $functionalCode;
	private $functionalLabel;
	
    public function __construct()
    {
		$this->setHttpStatus(200);
	}
	
	function getHttpStatus(){
		return $this->httpStatus;
	}
	function setHttpStatus($httpStatus){
		$this->httpStatus=$httpStatus;
	}
		
	function getHttpLabel(){
		return $this->httpLabel;
	}
	function setHttpLabel($httpLabel){
		$this->httpLabel=$httpLabel;
	}

	
	function getFunctionalCode(){
		return $this->functionalCode;
	}
	function setFunctionalCode($functionalCode){
		$this->functionalCode=$functionalCode;
	}

	
	function getFunctionalLabel(){
		return $this->functionalLabel;
	}
	function setFunctionalLabel($functionalLabel){
		$this->functionalLabel=$functionalLabel;
	}
	

}
