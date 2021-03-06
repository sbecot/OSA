<?php
/*--------------------------------------------------------
 * Module Name : ApplianceManager
 * Version : 2.0.0
 *
 * Software Name : OpenServicesAccess
 * Version : 2.2
 *
 * Copyright (c) 2011 – 2014 Orange
 * This software is distributed under the Apache 2 license
 * <http://www.apache.org/licenses/LICENSE-2.0.html>
 *
 *--------------------------------------------------------
 * File Name   : ApplianceManager/ApplianceManager.php/logs/logDAO.php
 *
 * Created     : 2013-11
 * Authors     : Benoit HERARD <benoit.herard(at)orange.com>
 * 				 zorglub42 <conntact(at)zorglub42.fr>
 *
 * Description :
 *      Manage database access for logs
 *--------------------------------------------------------
 * History     :
 * 1.0.0 - 2013-11-12 : Release of the file
 * 2.0.0 - 2015-08-02 : Update to PDO
*/
require_once '../objects/Error.class.php';
require_once '../objects/Log.class.php';
require_once '../include/Constants.php';
require_once '../include/Func.inc.php';
require_once '../include/Settings.ini.php';
require_once '../include/PDOFunc.php';
require_once '../include/PaginationFunc.inc.php';




function getLogs($id = NULL, $request_data = NULL){
	GLOBAL $BDName;
	GLOBAL $BDUser;
	GLOBAL $BDPwd;

	$error = new Error();


	$servicePart="";
	$userPart="";
	$statusPart="";
	$messagePart="";
	$frontEndEndPointPart="";
	$fromPart="";
	$untilPart="";
	$offset=0;
	$paginated=0;
	$orderPart="";

	$error->setHttpStatus(200);

	if (isset($request_data["serviceName"])){
		$servicePart = preg_replace("/\*/","%",$request_data["serviceName"]);
	}
	if (isset($request_data["userName"])){
		$userPart=preg_replace("/\*/","%",$request_data["userName"]);
	}
	if (isset($request_data["status"])){
		$statusPart=$request_data["status"];
	}
	if (isset($request_data["message"])){
		$messagePart=preg_replace("/\*/", "%",$request_data["message"]);
	}
	if (isset($request_data["frontEndEndPoint"])){
		$frontEndEndPointPart=preg_replace("/\*/", "%",$request_data["frontEndEndPoint"]);
	}
	if (isset($request_data["from"])){
		try {
			$fromPart=getDateFromIso($request_data["from"]);
		}catch (Exception $e){
			$error->setHttpStatus(400);
			$error->setFunctionalCode(2);
			$error->setFunctionalLabel($error->getFunctionalLabel() . $e->getMessage() . "for parameter from" );
		}
	}
	if (isset($request_data["until"])){
		try {
			$untilPart=getDateFromIso($request_data["until"]);
		}catch (Exception $e){
			$error->setHttpStatus(400);
			$error->setFunctionalCode(2);
			$error->setFunctionalLabel($error->getFunctionalLabel() . $e->getMessage() . "for parameter until" );
		}
	}
	if (isset($request_data["offset"])){
		$offset=$request_data["offset"] ;
	}
	if (isset($request_data["order"])){
		$orderPart=$request_data["order"];
	}

	if ($error->getHttpStatus() != 200){
		throw new Exception($error->GetFunctionalLabel(), $error->getHttpStatus());
	}


	try {
		$db=openDB($BDName, $BDUser, $BDPwd);

			if ($id != NULL){
				$strSQL = "SELECT * FROM hits WHERE id=?";
				$stmt=$db->prepare($strSQL);
				$stmt->execute(array($id));
				
				$row=$stmt->fetch(PDO::FETCH_ASSOC);
				if ($row){
					$log=new Log($row);
					$rc = $log->toArray();
				}
			}else{
				$where="";
				$bindPrms=array();
				if ($servicePart != ""){
					$where="serviceName like ?";
					array_push($bindPrms, $servicePart);
				}
				if ($userPart != ""){
						if ($where != ""){
							$where = $where . " AND ";
						}
						$where= $where . "userName like ?";
						array_push ($bindPrms, $userPart); 
				}else{
					if (isset($request_data["userName"])){
						if ($where != ""){
							$where = $where . " AND ";
						}
						$where = $where . " userName = ?";
						array_push ($bindPrms, $request_data["userName"]); 
					}
				}
				if ($statusPart != ""){
						if ($where != ""){
							$where = $where . " AND ";
						}
						$where= $where . "status=?" ;
						array_push($bindPrms, $statusPart);
				}
				if ($messagePart != ""){
						if ($where != ""){
							$where = $where . " AND ";
						}
						$where= $where . "message like ?" ;
						array_push($bindPrms, $messagePart);
				}
				if ($fromPart != ""){
						if ($where != ""){
							$where = $where . " AND ";
						}
						$where= $where . "timestamp>=?" ;
						array_push($bindPrms,  $fromPart );
				}
				if ($untilPart != ""){
						if ($where != ""){
							$where = $where . " AND ";
						}
						$where= $where . "timestamp<=?" ;
						array_push($bindPrms, $untilPart);
				}
				if ($frontEndEndPointPart != ""){
						if ($where != ""){
							$where = $where . " AND ";
						}
						$where= $where . "frontEndEndPoint like ?" ;
						array_push ($bindPrms,  $frontEndEndPointPart);
				}
				$strSQL = "SELECT * FROM hits ";
				if ($where != ""){
						$strSQL = $strSQL . " WHERE " .$where;
				}
				if ($orderPart ){
					$strSQL = $strSQL . " ORDER BY " . $orderPart;
				}
				$strSQL = $strSQL . " LIMIT " . $offset * recordCountPerPage . ", " . recordCountPerPage;
				$strSQLCount="SELECT count(id)  as count FROM hits";
				if ($where != ""){
						$strSQLCount = $strSQLCount . " WHERE " .$where;
				}

				$stmt=$db->prepare($strSQLCount);
				$stmt->execute($bindPrms);
				
				$row=$stmt->fetch(PDO::FETCH_ASSOC);
				$count=$row["count"];


				$stmt=$db->prepare($strSQL);
				$stmt->execute($bindPrms);
				$logs=Array();
				$logsCount=0;
				while ($row=$stmt->fetch(PDO::FETCH_ASSOC)){
					$logsCount++;
					$log = new Log($row);
					array_push($logs, $log->toArray());
				}
				$rc=Array(
						"length" => $count,
						"previous"=> generatePreviousLink("logs", $offset),
						"logs" => $logs,
						"next" => generateNextLink("logs", $offset, $logsCount, $count)
					);
			}


	}catch (Exception $e){
		$error->setHttpStatus(500);
		$error->setFunctionalCode(3);
		$error->setFunctionalLabel($e->getMessage());
		throw new Exception($error->GetFunctionalLabel(), $error->getHttpStatus());
	}
	
	return $rc;
}
