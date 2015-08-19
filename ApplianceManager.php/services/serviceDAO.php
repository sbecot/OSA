<?php
/*--------------------------------------------------------
 * Module Name : ApplianceManager
 * Version : 2.0.0
 *
 * Software Name : OpenServicesAccess
 * Version : 2.0
 *
 * Copyright (c) 2011 – 2014 Orange
 * This software is distributed under the Apache 2 license
 * <http://www.apache.org/licenses/LICENSE-2.0.html>
 *
 *--------------------------------------------------------
 * File Name   : ApplianceManager/ApplianceManager.php/groups/groupDAO.php
 *
 * Created     : 2013-11
 * Authors     : Benoit HERARD <benoit.herard(at)orange.com>
 * 				 zorglub42 <conntact(at)zorglub42.fr>
 *
 * Description :
 *      Manage database access for group object
 *--------------------------------------------------------
 * History     :
 * 1.0.0 - 2013-11-12 : Release of the file
 * 2.0.0 - 2015-08-02 : Update to PDO
*/
require_once '../objects/Error.class.php';
require_once '../objects/Service.class.php';
require_once '../objects/Node.class.php';
require_once '../objects/Quota.class.php';
require_once '../objects/User.class.php';
require_once '../include/Constants.php';
require_once '../include/PDOFunc.php';
require_once '../include/Func.inc.php';
require_once '../include/Settings.ini.php';
require_once '../include/BDMySQL.inc';
require_once '../include/BDControls.inc';


function getService($serviceName = NULL, $request_data=NULL){
	GLOBAL $BDName;
	GLOBAL $BDUser;
	GLOBAL $BDPwd;

	$serviceName=normalizeName($serviceName);

	$error = new Error();
	$error->setHttpStatus(200);

	try{
		$db=openDB($BDName, $BDUser, $BDPwd);
		if ($serviceName != NULL && $serviceName != ""){
			$strSQL = "SELECT * FROM services WHERE serviceName=?";
			$stmt=$db->prepare($strSQL);
			$stmt->execute(array(cut($serviceName, SERVICENAME_LENGTH)));
			
			if ($row=$stmt->fetch(PDO::FETCH_ASSOC)){
				$service = new Service($row);
				$rc=$service->toArray();
			}else{
				$error->setHttpStatus(404);
				$error->setHttpLabel("Unknown service");
				$error->setFunctionalCode(4);
				$error->setFunctionalLabel("Service ". $serviceName . " does not exists");
				throw new Exception($error->GetFunctionalLabel(), $error->getHttpStatus());;
			}
		}else{
			$strSQLComp="";
			$bindPrms=array();
			if (isset($request_data["withLog"])){
				$strSQLComp=" WHERE exists (SELECT 'x' FROM hits h WHERE h.serviceName=s.serviceName)";
			}
			if (isset($request_data["withQuotas"])){
				$strSQLComp=addSQLFilter("(isGlobalQuotasEnabled=1 or isUserQuotasenabled=1)",$strSQLComp);
			}
			if (isset($request_data["groupNameFilter"]) && $request_data["groupNameFilter"]!=""){
				$strSQLComp = addSQLFilter("groupName like ?", $strSQLComp);
				array_push($bindPrms, "%" . c($request_data["groupNameFilter"]) . "%");
			}
			if (isset($request_data["serviceNameFilter"]) && $request_data["serviceNameFilter"]){
				$strSQLComp = addSQLFilter("serviceName like ?", $strSQLComp);
				array_push($bindPrms, "%" . cut($request_data["serviceNameFilter"]) . "%");
			}
			if (isset($request_data["isIdentityForwardingEnabledFilter"]) && $request_data["isIdentityForwardingEnabledFilter"]!=""){
				$strSQLComp = addSQLFilter("isIdentityForwardingEnabled =?" , $strSQLComp);
				array_push($bindPrms, $request_data["isIdentityForwardingEnabledFilter"]);
			}
			if (isset($request_data["isGlobalQuotasEnabledFilter"]) && $request_data["isGlobalQuotasEnabledFilter"]!=""){
				$strSQLComp = addSQLFilter("isGlobalQuotasEnabled =?"  , $strSQLComp);
				array_push($bindPrms, $request_data["isGlobalQuotasEnabledEnabledFilter"]);
			}
			if (isset($request_data["isUserQuotasEnabledFilter"]) && $request_data["isUserQuotasEnabledFilter"]!=""){
				$strSQLComp = addSQLFilter("isUserQuotasEnabled =?"   , $strSQLComp);
				array_push($bindPrms, $request_data["isUserQuotasEnabledFilter"]);
			}
			if (isset($request_data["isPublishedFilter"]) && $request_data["isPublishedFilter"]!=""){
				$strSQLComp = addSQLFilter("isPublished =?"  , $strSQLComp);
				array_push($bindPrms, $request_data["isPublishedFilter"]);
			}
			if (isset($request_data["isHitLoggingEnabledFilter"]) && $request_data["isHitLoggingEnabledFilter"]!=""){
				$strSQLComp = addSQLFilter("isHitLoggingEnabled =?"  , $strSQLComp);
				array_push($bindPrms, $request_data["isHitLoggingEnabledFilter"]);
			}
			if (isset($request_data["isUserAuthenticationEnabledFilter"]) && $request_data["isUserAuthenticationEnabledFilter"]!=""){
				$strSQLComp = addSQLFilter("isUserAuthenticationEnabled =?"   , $strSQLComp);
				array_push($bindPrms, $request_data["isUserAuthenticationEnabledFilter"]);
			}
			if (isset($request_data["frontEndEndPointFilter"]) && $request_data["frontEndEndPointFilter"]!=""){
				$strSQLComp = addSQLFilter("frontEndEndPoint like ?", $strSQLComp);
				array_push($bindPrms, "%" . cut($request_data["frontEndEndPointFilter"]) . "%");
			}
			if (isset($request_data["backEndEndPointFilter"]) && $request_data["backEndEndPointFilter"]!=""){
				$strSQLComp = addSQLFilter("backEndEndPoint like ?", $strSQLComp);
				array_push($bindPrms, "%" . cut($request_data["backEndEndPointFilter"]) . "%");
			}
			if (isset($request_data["additionalConfigurationFilter"]) && $request_data["additionalConfigurationFilter"]!=""){
				$strSQLComp = addSQLFilter("additionalConfiguration like ?", $strSQLComp);
				array_push($bindPrms, "%" . cut($request_data["additionalConfigurationFilter"]) . "%");
			}
			
			$strSQL="SELECT * FROM services s" . $strSQLComp;
			if (isset($request_data["order"]) && $request_data["order"] != ""){
				$strSQL=$strSQL . " ORDER BY " . EscapeOrder($request_data["order"]);
			}
			
			$stmt=$db->prepare($strSQL);
			$stmt->execute($bindPrms);
			$rc =  Array();
			while ($row=$stmt->fetch(PDO::FETCH_ASSOC)){
				$service = new Service($row);
				array_push($rc,$service->toArray());
			}
		}
	}catch (Exception $e){
		if ($error->getHttpStatus() ==200){
			$error->setHttpStatus(500);
			$error->setFunctionalLabel($e->getMessage());
		}
		$error->setFunctionalCode(3);
		throw new Exception($error->GetFunctionalLabel(), $error->getHttpStatus());;
		
	}
	return $rc;

}






function createService($serviceName = NULL, $request_data=NULL){
	GLOBAL $BDName;
	GLOBAL $BDUser;
	GLOBAL $BDPwd;

	$serviceName=normalizeName($serviceName);

	$error = new Error();
	$error->setHttpStatus(200);
	$error->setHttpLabel("Bad request for method \"" . $_SERVER["REQUEST_METHOD"] . "\" for resource \"service\"");


	$error->setFunctionalLabel("Bad request for method \"" . $_SERVER["REQUEST_METHOD"] . "\" for resource \"service\"\n");
	$error->setFunctionalCode(0);


	$mySQLIsUserAuthenticationEnabled=1;
	$mySQLIsHitLoggingEnabled=0;
	$mySQLIsAnonymousAllowed=0;
	$mySQLOnAllNodes=1;
	$mySQLLoginFormUri="''";

	if (isset($request_data["additionalConfiguration"])){
		$mySQLAdditionalConfiguration=$request_data["additionalConfiguration"];
	}else{
		$mySQLAdditionalConfiguration="''";
	}
	if (isset($request_data["isAnonymousAllowed"])){
		if ($request_data["isAnonymousAllowed"]=="1" ||  $request_data["isAnonymousAllowed"]=="0"){
			$mySQLIsAnonymousAllowed=$request_data["isAnonymousAllowed"];
		}else{
			$error->setHttpStatus(400);
			$error->setFunctionalCode(1);
			$error->setFunctionalLabel($error->getFunctionalLabel() . " allowed value for isAnonymousAllowed is 0 or 1\n");
		}
	}
	if (isset($request_data["isHitLoggingEnabled"])){
		if ($request_data["isHitLoggingEnabled"]=="1" ||  $request_data["isHitLoggingEnabled"]=="0"){
			$mySQLIsHitLoggingEnabled=$request_data["isHitLoggingEnabled"];
		}else{
			$error->setHttpStatus(400);
			$error->setFunctionalCode(1);
			$error->setFunctionalLabel($error->getFunctionalLabel() . " allowed value for isHitLoggingEnabled is 0 or 1\n");
		}
	}
	if (isset($request_data["onAllNodes"])){
		if ($request_data["onAllNodes"]=="1" ||  $request_data["onAllNodes"]=="0"){
			$mySQLOnAllNodes=$request_data["onAllNodes"];
		}else{
			$error->setHttpStatus(400);
			$error->setFunctionalCode(1);
			$error->setFunctionalLabel($error->getFunctionalLabel() . " allowed value for onAllNodes is 0 or 1\n");
		}
	}
	if (isset($request_data["isUserAuthenticationEnabled"])){
		if ($request_data["isUserAuthenticationEnabled"]=="1" ||  $request_data["isUserAuthenticationEnabled"]=="0"){
			$mySQLIsUserAuthenticationEnabled=$request_data["isUserAuthenticationEnabled"];
		}else{
			$error->setHttpStatus(400);
			$error->setFunctionalCode(1);
			$error->setFunctionalLabel($error->getFunctionalLabel() . " allowed value for isUserAuthenticationEnabled is 0 or 1\n");
		}
	}
		

		


	if ($serviceName == NULL || $serviceName=="" ){
		$error->setHttpStatus(400);
		$error->setFunctionalCode(1);
		$error->setFunctionalLabel($error->getFunctionalLabel() . "serviceName is required\n");
	}else{
		$mySQLServiceName=cut($serviceName, SERVICENAME_LENGTH);
	}

	if (isset($request_data["isPublished"])){
		if ($request_data["isPublished"]=="1" ||  $request_data["isPublished"]=="0"){
			$mySQLIsPublished=$request_data["isPublished"];
		}else{
			$error->setHttpStatus(400);
			$error->setFunctionalCode(1);
			$error->setFunctionalLabel($error->getFunctionalLabel() . " allowed value for isPublished is 0 or 1\n");
		}
	}else{
			$error->setHttpStatus(400);
			$error->setFunctionalCode(1);
			$error->setFunctionalLabel($error->getFunctionalLabel() . " isPublished is required\n");
	}


	if (!isset($request_data["isGlobalQuotasEnabled"]) || $request_data["isGlobalQuotasEnabled"]=="" ){
		$error->setHttpStatus(400);
		$error->setFunctionalCode(1);
		$error->setFunctionalLabel($error->getFunctionalLabel() . "isGlobalQuotasEnabled is required\n");
	}elseif ($request_data["isGlobalQuotasEnabled"]=="1" || $request_data["isGlobalQuotasEnabled"]=="0"  ){
		$mySQLGlobalQuotas=$request_data["isGlobalQuotasEnabled"];
	}else{
		$error->setHttpStatus(400);
		$error->setFunctionalCode(1);
		$error->setFunctionalLabel($error->getFunctionalLabel() . " allowed values for isGlobalQuotasEnabled is 0 or 1\n");
	}
	$mySQLReqSec=0;
	$mySQLReqDay=0;
	$mySQLReqMonth=0;

	if (!isset($request_data["isGlobalQuotasEnabled"]) || $request_data["isGlobalQuotasEnabled"]=="1" ){
		if (!isset($request_data["reqSec"]) || $request_data["reqSec"]=="" ){
			$error->setHttpStatus(400);
			$error->setFunctionalCode(1);
			$error->setFunctionalLabel($error->getFunctionalLabel() . "reqSec is required when isGlobalQuotasEnabled=1\n");
		}else{
			$mySQLReqSec=$request_data["reqSec"];
		}
		if (!isset($request_data["reqDay"]) || $request_data["reqDay"]=="" ){
			$error->setHttpStatus(400);
			$error->setFunctionalCode(1);
			$error->setFunctionalLabel($error->getFunctionalLabel() . "reqDay is required when isGlobalQuotasEnabled=1\n");
		}else{
			$mySQLReqDay=$request_data["reqDay"];
		}
		if (!isset($request_data["reqMonth"]) || $request_data["reqMonth"]=="" ){
			$error->setHttpStatus(400);
			$error->setFunctionalCode(1);
			$error->setFunctionalLabel($error->getFunctionalLabel() . "reqMonth is required when isGlobalQuotasEnabled=1\n");
		}else{
			$mySQLReqMonth=$request_data["reqMonth"];
		}
		
	}



	if (isset($request_data["isIdentityForwardingEnabled"]) && $request_data["isIdentityForwardingEnabled"]!="" ){
		if ($request_data["isIdentityForwardingEnabled"]=="0" || $request_data["isIdentityForwardingEnabled"]=="1"){
			$mySQLIdentityForwarding=$request_data["isIdentityForwardingEnabled"];
			if ($mySQLIsUserAuthenticationEnabled==0 && $request_data["isIdentityForwardingEnabled"]==1){
				$error->setHttpStatus(400);
				$error->setFunctionalCode(1);
				$error->setFunctionalLabel($error->getFunctionalLabel() . " isIdentityForwardingEnabled can not be set to 1 when isUserAuthentication is disabled\n");
			}
		}else{
			$error->setHttpStatus(400);
			$error->setFunctionalCode(1);
			$error->setFunctionalLabel($error->getFunctionalLabel() . " allowed values for isIdentityForwardingEnabled is 0 or 1\n");
		}
			
	}else{
		$mySQLIdentityForwarding=0;
	}

	if (isset($request_data["backEndUsername"])){
			$mySQLBackEndUsername=cut($request_data["backEndUsername"], BACKENDENDPOINT_LENGTH);
	}else{
		$mySQLBackEndUsername=null;
	}
	if (isset($request_data["backEndPassword"])){
			$mySQLBackEndPassword="'" . encrypt($request_data["backEndPassword"]) . "'";
	}else{
		$mySQLBackEndPassword=null;
	}

	if (isset($request_data["isUserQuotasEnabled"])){
		if ($request_data["isUserQuotasEnabled"]=="0" ||  $request_data["isUserQuotasEnabled"]=="1" ){
			$mySQLUserQuotas=$request_data["isUserQuotasEnabled"];
			if ($mySQLIsUserAuthenticationEnabled==0 && $request_data["isUserQuotasEnabled"]==1){
				$error->setHttpStatus(400);
				$error->setFunctionalCode(1);
				$error->setFunctionalLabel($error->getFunctionalLabel() . " isUserQuotasEnabled can not be set to 1 when isUserAuthentication is disabled\n");
			}
		}else{
			$error->setHttpStatus(400);
			$error->setFunctionalCode(1);
			$error->setFunctionalLabel($error->getFunctionalLabel() . " allowed value for isUSerquotasEnabled is 0 or 1\n");
		}
	}else{
		$mySQLUserQuotas=0;
	}
	if (!isset($request_data["frontEndEndPoint"]) || $request_data["frontEndEndPoint"]=="" ){
		$error->setHttpStatus(400);
		$error->setFunctionalCode(1);
		$error->setFunctionalLabel($error->getFunctionalLabel() . "frontEndEndPoint is required\n");
	}else{
		$mySQLFrontEndEndPoint=cut($request_data["frontEndEndPoint"], FRONTENDENDPOINT_LENGTH);
	}
	if (!isset($request_data["backEndEndPoint"]) || $request_data["backEndEndPoint"]=="" ){
		$error->setHttpStatus(400);
		$error->setFunctionalCode(1);
		$error->setFunctionalLabel($error->getFunctionalLabel() . "backEndEndPoint is required\n");
	}elseif (ereg("^(http|https|ws)://[\w\d:#@%/;$()~_?\+-=\\\.&]*", $request_data["backEndEndPoint"])){
		$mySQLBackEndEndPoint=cut($request_data["backEndEndPoint"], BACKENDENDPOINT_LENGTH);
	}else{
		$error->setHttpStatus(400);
		$error->setFunctionalCode(1);
		$error->setFunctionalLabel($error->getFunctionalLabel() . $request_data["backEndEndPoint"] . " is not a valid URL for backend service\n");
	}
	if (isset($request_data["loginFormUri"]) && $request_data["loginFormUri"]!="" ){
		$mySQLLoginFormUri=cut($request_data["loginFormUri"]) ;
	}

	if (isset($request_data["groupName"])){
		if ($request_data["groupName"]!="" ){
				$mySQLGroupName=cut($request_data["groupName"], GROUPNAME_LENGTH) ;
		}else if ($mySQLIsUserAuthenticationEnabled==1){
			$error->setHttpStatus(400);
			$error->setFunctionalCode(1);
			$error->setFunctionalLabel($error->getFunctionalLabel() . " value for groupName is required\n");
		}
	}else{
		$mySQLGroupName=null;
	}

	if ($mySQLIsUserAuthenticationEnabled==0){
		$mySQLGroupName=null;
		$mySQLUserQuotas=0;
		$mySQLIdentityForwarding=0;
	}



	if ($mySQLIsAnonymousAllowed==1){
		$mySQLIdentityForwarding=1;
	}


	if ($error->getHttpStatus() != 200){
		throw new Exception($error->GetFunctionalLabel(), $error->getHttpStatus());
	}else{
		try {

		
			$db=openDB($BDName, $BDUser, $BDPwd );
			
			$strSQL = "";
			$strSQL = $strSQL . "INSERT INTO services (";
			$strSQL = $strSQL . "	serviceName, ";
			$strSQL = $strSQL . "	reqSec,";
			$strSQL = $strSQL . "	 reqDay,";
			$strSQL = $strSQL . "	 reqMonth,";
			$strSQL = $strSQL . "	 frontEndEndPoint,";
			$strSQL = $strSQL . "	 isGlobalQuotasEnabled,";
			$strSQL = $strSQL . "	 isUserQuotasEnabled,";
			$strSQL = $strSQL . "	 isIdentityForwardingEnabled,";
			$strSQL = $strSQL . "	 groupName,";
			$strSQL = $strSQL . "	 backEndEndPoint,";
			$strSQL = $strSQL . "	 backEndUsername,";
			$strSQL = $strSQL . "	 backEndPassword,";
			$strSQL = $strSQL . "	 isHitLoggingEnabled,";
			$strSQL = $strSQL . "	 isAnonymousAllowed,";
			$strSQL = $strSQL . "	 isUserAuthenticationEnabled,";
			$strSQL = $strSQL . "	 additionalConfiguration,";
			$strSQL = $strSQL . "	 onAllNodes,";
			$strSQL = $strSQL . "	 loginFormUri";

			$strSQL = $strSQL . ") values (";
			$strSQL = $strSQL . "	?,";
			$strSQL = $strSQL . "	? ,";
			$strSQL = $strSQL . "	? ,";
			$strSQL = $strSQL . "	? ,";
			$strSQL = $strSQL . "	?,";
			$strSQL = $strSQL . "	?,";
			$strSQL = $strSQL . "	?,";
			$strSQL = $strSQL . "	?,";
			$strSQL = $strSQL . "	?,";
			$strSQL = $strSQL . "	?,";
			$strSQL = $strSQL . "	?,";
			$strSQL = $strSQL . "	?,";
			$strSQL = $strSQL . "	?,";
			$strSQL = $strSQL . "	?,";
			$strSQL = $strSQL . "	?,";
			$strSQL = $strSQL . "	?,";
			$strSQL = $strSQL . "	?,";
			$strSQL = $strSQL . "	?)";

			$stmt=$db->prepare($strSQL);
			$stmt->execute(array(	$mySQLServiceName,
									$mySQLReqSec ,
									$mySQLReqDay ,
									$mySQLReqMonth ,
									$mySQLFrontEndEndPoint,
									$mySQLGlobalQuotas,
									$mySQLUserQuotas,
									$mySQLIdentityForwarding,
									$mySQLGroupName,
									$mySQLBackEndEndPoint,
									$mySQLBackEndUsername,
									$mySQLBackEndPassword,
									$mySQLIsHitLoggingEnabled,
									$mySQLIsAnonymousAllowed,
									$mySQLIsUserAuthenticationEnabled,
									$mySQLAdditionalConfiguration,
									$mySQLOnAllNodes,
									$mySQLLoginFormUri
								)
			);
			if (applyApacheConfiguration()){
				return getService($serviceName);
			}else{
				$error->setHttpStatus(500);
				$error->setFunctionalCode(1);
				$error->setFunctionalLabel("Service successfully saved but unable to apply configuration on runtime appliance");
				throw new Exception($error->GetFunctionalLabel(), $error->getHttpStatus());;
			}
		}catch (Exception $e){
			if (strpos($e->getMessage,"Duplicate entry")){
				$error->setHttpStatus(409);
				$error->setFunctionalCode(5);
				$error->setFunctionalLabel("Service " . $serviceName . " already exists or a sevice with " . $request_data["frontEndEndPoint"] . " as front end URI already exists" );
			}elseif (strpos($e->getMessage(),"a foreign key constraint fails")){
				$error->setHttpStatus(404);
				$error->setFunctionalCode(5);
				$error->setFunctionalLabel("The group " . $request_data["groupName"] . " does not exists");
			}else{
				$error->setHttpStatus(500);
				$error->setFunctionalCode(3);
				$error->setFunctionalLabel($e->getMessage());
			}
			throw new Exception($error->GetFunctionalLabel(), $error->getHttpStatus());
		}
	}
}





function deleteService($serviceName = NULL){
	GLOBAL $BDName;
	GLOBAL $BDUser;
	GLOBAL $BDPwd;
	$error = new Error();

	$serviceName=normalizeName($serviceName);

	$cnx = new Connexion();
	if (!$cnx->Ouvrir($BDName, $BDUser, $BDPwd)){
			$error->setHttpStatus(500);
			$error->setFunctionalCode(3);
			$error->setFunctionalLabel($cnx->GetErreur()->GetTexte());
			throw new Exception($error->GetFunctionalLabel(), $error->getHttpStatus());;
		
	}

	$rqt = new RequeteResultat();
	if ($serviceName != NULL && $serviceName != ""){
		$strSQL = "SELECT * FROM services WHERE serviceName='" . DoubleQuote($serviceName, SERVICENAME_LENGTH) . "'";
		if (!$rqt->Ouvrir($strSQL, $cnx)){
			$error->setHttpStatus(500);
			$error->setFunctionalCode(3);
			$error->setFunctionalLabel($rqt->Erreur->GetTexte());
			$cnx->Fermer();
			throw new Exception($error->GetFunctionalLabel(), $error->getHttpStatus());;			
		}
		
		if ($rqt->EOF()){
			$error->setHttpStatus(404);
			$error->setHttpLabel("Unknown service");
			$error->setFunctionalCode(4);
			$error->setFunctionalLabel("Service ". $serviceName . " does not exists");
			$rqt->Fermer();
			$cnx->Fermer();
			throw new Exception($error->GetFunctionalLabel(), $error->getHttpStatus());;
		}else{
			if (startsWith($serviceName, ADMIN_SERVICE)){
				$error->setHttpStatus(403);
				$error->setFunctionalCode(3);
				$error->setFunctionalLabel($serviceName . " service can't be suppressed");
				$rqt->Fermer();
				$cnx->Fermer();
				throw new Exception($error->GetFunctionalLabel(), $error->getHttpStatus());;
			}
			$service = new Service($rqt);
			$strSQL="DELETE FROM services WHERE  serviceName='" . DoubleQuote($serviceName, GROUPNAME_LENGTH) . "'";
			if (!$rqt->Executer($strSQL, $cnx)){
				$error->setHttpStatus(500);
				$error->setFunctionalCode(3);
				$error->setFunctionalLabel($rqt->Erreur->GetTexte());
				if (strpos($rqt->Erreur->GetTexte(),"a foreign key constraint fails")){
					$error->setFunctionalLabel("The service " . $serviceName . " is used by some users. Please remove subscribtions to it first");
				}
				$rqt->Fermer();
				$cnx->Fermer();
				throw new Exception($error->GetFunctionalLabel(), $error->getHttpStatus());
				
			}
			$strSQL="DELETE FROM counters WHERE  counterName like 'R=" . DoubleQuote($serviceName, SERVICENAME_LENGTH) . "%'";
			if (!$rqt->Executer($strSQL, $cnx)){
				$error->setHttpStatus(500);
				$error->setFunctionalCode(3);
				$error->setFunctionalLabel($rqt->Erreur->GetTexte());
				$rqt->Ferme();
				$cnx->Fermer();
				throw new Exception($error->GetFunctionalLabel(), $error->getHttpStatus());
				
			}
			if (applyApacheConfiguration()){
				
				$rc = $service->toArray();
			}else{
				$error->setHttpStatus(500);
				$error->setFunctionalCode(1);
				$error->setFunctionalLabel("Service successfully saved but unable to apply configuration on runtime appliance");
				$rqt->Fermer();
				$cnx->Fermer();
				throw new Exception($error->GetFunctionalLabel(), $error->getHttpStatus());
			}
			
		}
		$rqt->Fermer();
	}else{
		$error->setHttpLabel("Bad request for method \"" . $_SERVER["REQUEST_METHOD"] . "\" for resource \"service\"");
		$error->setHttpStatus(400);
		$error->setFunctionalCode(1);
		$error->setFunctionalLabel($error->getFunctionalLabel() . "serviceName is required\n");
		$cnx->Fermer();
		throw new Exception($error->GetFunctionalLabel(), $error->getHttpStatus());
	}
	$cnx->Fermer();
	return $rc;
}







function updateService($serviceName = NULL, $request_data=NULL){
	GLOBAL $BDName;
	GLOBAL $BDUser;
	GLOBAL $BDPwd;

	$serviceName=normalizeName($serviceName);

	$error = new Error();
	$error->setHttpStatus(200);
	$error->setHttpLabel("Bad request for method \"" . $_SERVER["REQUEST_METHOD"] . "\" for resource \"service\"");


	$error->setFunctionalLabel("Bad request for method \"" . $_SERVER["REQUEST_METHOD"] . "\" for resource \"service\"\n");
	$error->setFunctionalCode(0);



	if ($serviceName == NULL || $serviceName=="" ){
		$error->setHttpStatus(400);
		$error->setFunctionalCode(1);
		$error->setFunctionalLabel($error->getFunctionalLabel() . "serviceName is required\n");
	}else{
		$serviceName=str_replace(" ", "_", $serviceName);
		$mySQLServiceName="'" . DoubleQuote($serviceName, SERVICENAME_LENGTH) . "'";
	}
	$service=getService($serviceName);


	if (isset($request_data["additionalConfiguration"])){
		$service["additionalConfiguration"]=$request_data["additionalConfiguration"];
	}


	if (isset($request_data["isHitLoggingEnabled"])){
		if ($request_data["isHitLoggingEnabled"]=="1" ||  $request_data["isHitLoggingEnabled"]=="0"){
			$service["isHitLoggingEnabled"]=$request_data["isHitLoggingEnabled"];
		}else{
			$error->setHttpStatus(400);
			$error->setFunctionalCode(1);
			$error->setFunctionalLabel($error->getFunctionalLabel() . " allowed value for isHitLoggingEnabled is 0 or 1\n");
		}
	}
	if (isset($request_data["isAnonymousAllowed"])){
		if ($request_data["isAnonymousAllowed"]=="1" ||  $request_data["isAnonymousAllowed"]=="0"){
			$service["isAnonymousAllowed"]=$request_data["isAnonymousAllowed"];
		}else{
			$error->setHttpStatus(400);
			$error->setFunctionalCode(1);
			$error->setFunctionalLabel($error->getFunctionalLabel() . " allowed value for isAnonymousAllowed is 0 or 1\n");
		}
	}
	if (isset($request_data["isUserAuthenticationEnabled"])){
		if ($request_data["isUserAuthenticationEnabled"]=="1" ||  $request_data["isUserAuthenticationEnabled"]=="0"){
			if ($request_data["isUserAuthenticationEnabled"]=="1" && (!isset($request_data["groupName"]) ||  $request_data["groupName"]=="")){
				$error->setHttpStatus(400);
				$error->setFunctionalCode(1);
				$error->setFunctionalLabel($error->getFunctionalLabel() . " value for groupName is required\n");
			}else{
				$service["isUserAuthenticationEnabled"]=$request_data["isUserAuthenticationEnabled"];
			}
		}else{
			$error->setHttpStatus(400);
			$error->setFunctionalCode(1);
			$error->setFunctionalLabel($error->getFunctionalLabel() . " allowed value for isUserAuthenticationEnabled is 0 or 1\n");
		}
	}
		

		


	if (isset($request_data["isPublished"])){
		if ($request_data["isPublished"]=="1" ||  $request_data["isPublished"]=="0"){
			$service["isPublished"]=$request_data["isPublished"];
		}else{
			$error->setHttpStatus(400);
			$error->setFunctionalCode(1);
			$error->setFunctionalLabel($error->getFunctionalLabel() . " allowed value for isPublished is 0 or 1\n");
		}
	}
	if (isset($request_data["onAllNodes"])){
		if ($request_data["onAllNodes"]=="1" ||  $request_data["onAllNodes"]=="0"){
			$service["onAllNodes"]=$request_data["onAllNodes"];
		}else{
			$error->setHttpStatus(400);
			$error->setFunctionalCode(1);
			$error->setFunctionalLabel($error->getFunctionalLabel() . " allowed value for onAllNodes is 0 or 1\n");
		}
	}


	if (isset($request_data["isGlobalQuotasEnabled"]) && $request_data["isGlobalQuotasEnabled"]!="" ){
		if ($request_data["isGlobalQuotasEnabled"]=="1" || $request_data["isGlobalQuotasEnabled"]=="0"  ){
			$service["isGlobalQuotasEnabled"]=$request_data["isGlobalQuotasEnabled"];
		}else{
			$error->setHttpStatus(400);
			$error->setFunctionalCode(1);
			$error->setFunctionalLabel($error->getFunctionalLabel() . " allowed values for isGlobalQuotasEnabled is 0 or 1\n");
		}
	}

	if (!isset($request_data["isGlobalQuotasEnabled"]) || $request_data["isGlobalQuotasEnabled"]=="1" ){
		if (!isset($request_data["reqSec"]) || $request_data["reqSec"]=="" ){
			$error->setHttpStatus(400);
			$error->setFunctionalCode(1);
			$error->setFunctionalLabel($error->getFunctionalLabel() . "reqSec is required when isGlobalQuotasEnabled=1\n");
		}elseif (is_numeric($request_data["reqSec"]) && $request_data["reqSec"]>=1){
			$service["reqSec"]=$request_data["reqSec"];
		}else{
			$error->setHttpStatus(400);
			$error->setFunctionalCode(1);
			$error->setFunctionalLabel($error->getFunctionalLabel() . "reqSec should be an integer >=1\n");
		}
		if (!isset($request_data["reqDay"]) || $request_data["reqDay"]=="" ){
			$error->setHttpStatus(400);
			$error->setFunctionalCode(1);
			$error->setFunctionalLabel($error->getFunctionalLabel() . "reqDay is required when isGlobalQuotasEnabled=1\n");
		}elseif (is_numeric($request_data["reqDay"]) && $request_data["reqDay"]>=1){
			$service["reqDay"]=$request_data["reqDay"];
		}else{
			$error->setHttpStatus(400);
			$error->setFunctionalCode(1);
			$error->setFunctionalLabel($error->getFunctionalLabel() . "reqDay should be an integer >=1\n");
		}
		if (!isset($request_data["reqMonth"]) || $request_data["reqMonth"]=="" ){
			$error->setHttpStatus(400);
			$error->setFunctionalCode(1);
			$error->setFunctionalLabel($error->getFunctionalLabel() . "reqMonth is required when isGlobalQuotasEnabled=1\n");
		}elseif (is_numeric($request_data["reqMonth"]) && $request_data["reqMonth"]>=1){
			$service["reqMonth"]=$request_data["reqMonth"];
		}else{
			$error->setHttpStatus(400);
			$error->setFunctionalCode(1);
			$error->setFunctionalLabel($error->getFunctionalLabel() . "reqMonth should be an integer >=1\n");
		}
		
	}



	if (isset($request_data["isIdentityForwardingEnabled"]) && $request_data["isIdentityForwardingEnabled"]!="" ){
		if ($request_data["isIdentityForwardingEnabled"]=="0" || $request_data["isIdentityForwardingEnabled"]=="1"){
			$service["isIdentityForwardingEnabled"]=$request_data["isIdentityForwardingEnabled"];
			if ($service["isUserAuthenticationEnabled"]==0 && $request_data["isIdentityForwardingEnabled"]==1){
				$error->setHttpStatus(400);
				$error->setFunctionalCode(1);
				$error->setFunctionalLabel($error->getFunctionalLabel() . " isIdentityForwardingEnabled can not be set to 1 when isUserAuthentication is disabled\n");
			}
		}else{
			$error->setHttpStatus(400);
			$error->setFunctionalCode(1);
			$error->setFunctionalLabel($error->getFunctionalLabel() . " allowed values for isIdentityForwardingEnabled is 0 or 1\n");
		}
			
	}
	if (isset($request_data["backEndUsername"])){
			$service["backEndUsername"]=$request_data["backEndUsername"];
	}
	if (isset($request_data["backEndPassword"])){
			$service["backEndPassword"]=$request_data["backEndPassword"];
	}

	if (isset($request_data["isUserQuotasEnabled"])){
		if ($request_data["isUserQuotasEnabled"]=="0" ||  $request_data["isUserQuotasEnabled"]=="1" ){
			$service["isUserQuotasEnabled"]=$request_data["isUserQuotasEnabled"];
			if ($request_data["isUserAuthenticationEnabled"]==0 && $request_data["isUserQuotasEnabled"]==1){
				$error->setHttpStatus(400);
				$error->setFunctionalCode(1);
				$error->setFunctionalLabel($error->getFunctionalLabel() . " isUserQuotasEnabled can not be set to 1 when isUserAuthentication is disabled\n");
			}
		}else{
			$error->setHttpStatus(400);
			$error->setFunctionalCode(1);
			$error->setFunctionalLabel($error->getFunctionalLabel() . " allowed value for isUSerquotasEnabled is 0 or 1\n");
		}
	}
	if (isset($request_data["frontEndEndPoint"]) && $request_data["frontEndEndPoint"]!="" ){
		$service["frontEndEndPoint"]=$request_data["frontEndEndPoint"];
	}
	if (isset($request_data["backEndEndPoint"]) && $request_data["backEndEndPoint"]!="" ){
		if (ereg("^(http|https|ws)://[\w\d:#@%/;$()~_?\+-=\\\.&]*", $request_data["backEndEndPoint"])){
			$service["backEndEndPoint"]=$request_data["backEndEndPoint"];
		}else{
			$error->setHttpStatus(400);
			$error->setFunctionalCode(1);
			$error->setFunctionalLabel($error->getFunctionalLabel() . $request_data["backEndEndPoint"] . " is not a valid URL\n");
		}
	}
	if (isset($request_data["backEndEndPoint"]) && $request_data["backEndEndPoint"]!="" ){
		if (ereg("^(http|https|ws)://[\w\d:#@%/;$()~_?\+-=\\\.&]*", $request_data["backEndEndPoint"])){
			$service["backEndEndPoint"]=$request_data["backEndEndPoint"];
		}else{
			$error->setHttpStatus(400);
			$error->setFunctionalCode(1);
			$error->setFunctionalLabel($error->getFunctionalLabel() . $request_data["backEndEndPoint"] . " is not a valid URL\n");
		}
	}
	if (isset($request_data["groupName"]) && $request_data["groupName"]!="" ){
		$service["groupName"]=$request_data["groupName"];
	}
	if ($service["isUserAuthenticationEnabled"]==0){
		$service["groupName"]=null;
		$service["isUserQuotasEnabled"]=0;
		$service["isIdentityForwardingEnabled"]=0;
	}
	
	if ($service["isAnonymousAllowed"]==1){
		$service["isIdentityForwardingEnabled"]=1;
	}
	if (isset($request_data["loginFormUri"]) && $request_data["loginFormUri"]!="" ){
		$service["loginFormUri"]=$request_data["loginFormUri"];
	}


	if ($error->getHttpStatus() != 200){
		throw new Exception($error->GetFunctionalLabel(), $error->getHttpStatus());
	}else{
		$strSQL = "";
		$strSQL = $strSQL . "UPDATE services SET";
		$strSQL = $strSQL . "	 reqSec=" . $service["reqSec"] . ",";
		$strSQL = $strSQL . "	 reqDay=" . $service["reqDay"] . ",";
		$strSQL = $strSQL . "	 reqMonth=" . $service["reqMonth"] . ",";
		$strSQL = $strSQL . "	 frontEndEndPoint='" . DoubleQuote($service["frontEndEndPoint"]) . "',";
		$strSQL = $strSQL . "	 isGlobalQuotasEnabled=" . $service["isGlobalQuotasEnabled"] . ",";
		$strSQL = $strSQL . "	 isUserQuotasEnabled=" . $service["isUserQuotasEnabled"] . ",";
		$strSQL = $strSQL . "	 isIdentityForwardingEnabled=" . $service["isIdentityForwardingEnabled"] . ",";
		$strSQL = $strSQL . "	 isPublished=" . $service["isPublished"] . ",";
		$strSQL = $strSQL . "	 groupName=" . SQLString($service["groupName"]) . ",";
		$strSQL = $strSQL . "	 backEndEndPoint='" . DoubleQuote($service["backEndEndPoint"]) . "',";
		$strSQL = $strSQL . "	 backEndUsername='" . DoubleQuote($service["backEndUsername"]) . "',";
		$strSQL = $strSQL . "	 backEndPassword='" . DoubleQuote(encrypt($service["backEndPassword"])) . "',";
		$strSQL = $strSQL . "	 isHitLoggingEnabled=" . $service["isHitLoggingEnabled"] . ",";
		$strSQL = $strSQL . "	 isAnonymousAllowed=" . $service["isAnonymousAllowed"] . ",";
		$strSQL = $strSQL . "	 isUserAuthenticationEnabled=" . $service["isUserAuthenticationEnabled"] . ",";
		$strSQL = $strSQL . "	 onAllNodes=" . $service["onAllNodes"] . ",";
		$strSQL = $strSQL . "	 additionalConfiguration='" . DoubleQuote($service["additionalConfiguration"]) . "', ";
		$strSQL = $strSQL . "	 loginFormUri='" . DoubleQuote($service["loginFormUri"]) . "'";
		$strSQL = $strSQL . " WHERE serviceName=$mySQLServiceName";


		$cnx = new Connexion();
		
		if (!$cnx->Ouvrir($BDName, $BDUser, $BDPwd )){
			$error->setHttpStatus(500);
			$error->setFunctionalCode(3);
			$error->setFunctionalLabel($cnx->GetErreur()->GetTexte());
		}else{
			$rqt = new RequeteResultat();
			if (!$rqt->Executer($strSQL,$cnx)){
				if (strpos($rqt->Erreur->GetTexte(),"Duplicate entry")){
					$error->setHttpStatus(409);
					$error->setFunctionalCode(5);
					$error->setFunctionalLabel("Service " . $serviceName . " already exists");
					
				}else{
					$error->setHttpStatus(500);
					$error->setFunctionalCode(3);
					$error->setFunctionalLabel($rqt->Erreur->GetTexte());
					
					
				}
				if (strpos($rqt->Erreur->GetTexte(),"a foreign key constraint fails")){
					$error->setHttpStatus(404);
					$error->setFunctionalLabel("The group " . $request_data["groupName"] . " does not exists");
				}
			}
			$cnx->Fermer();
		}
		
		if ($error->getHttpStatus() != 200){
			throw new Exception($error->GetFunctionalLabel(), $error->getHttpStatus());
		}
		if (isset($request_data["noApply"])){
			return getService($serviceName);
		}else if (applyApacheConfiguration()){
			return getService($serviceName);
		}else{
			$error->setHttpStatus(500);
			$error->setFunctionalCode(1);
			$error->setFunctionalLabel("Service successfully saved but unable to apply configuration on runtime appliance");
			throw new Exception($error->GetFunctionalLabel(), $error->getHttpStatus());
		}
	}
}



function getUserQuotas($serviceName = NULL, $userName=NULL, $request_data=NULL){
	GLOBAL $BDName;
	GLOBAL $BDUser;
	GLOBAL $BDPwd;

	$serviceName=normalizeName($serviceName);
	$userName=normalizeName($userName);

	$error = new Error();

	if ($serviceName == NULL || $serviceName==""){
		$error->setHttpStatus(400);
		$error->setFunctionalCode(1);
		$error->setFunctionalLabel($error->getFunctionalLabel() . "serviceName is required\n");
		throw new Exception($error->GetFunctionalLabel(), $error->getHttpStatus());
	}

	$cnx = new Connexion();
	if (!$cnx->Ouvrir($BDName, $BDUser, $BDPwd)){
			$error->setHttpStatus(500);
			$error->setFunctionalCode(3);
			$error->setFunctionalLabel($cnx->GetErreur()->GetTexte());
			throw new Exception($error->GetFunctionalLabel(), $error->getHttpStatus());
		
	}



		$rqt = new RequeteResultat();
		$strSQL="SELECT * FROM usersquotas WHERE serviceName='" . DoubleQuote($serviceName, SERVICENAME_LENGTH ) . "'";
		if ($userName != NULL && $userName != ""){
			$strSQL .= " AND userName='" . DoubleQuote($userName, USERNAME_LENGTH) . "'";
			if (!$rqt->Ouvrir($strSQL, $cnx)){
				$error->setHttpStatus(500);
				$error->setFunctionalCode(3);
				$error->setFunctionalLabel($rqt->Erreur->GetTexte());
				$cnx->Fermer();
				throw new Exception($error->GetFunctionalLabel(), $error->getHttpStatus());
				
			}
			
			if ($rqt->EOF()){
				$error->setHttpStatus(404);
				$error->setHttpLabel("Unknown quotas");
				$error->setFunctionalCode(4);
				$error->setFunctionalLabel("Quotas for user ". $userName . " and service " . $serviceName . " does not exists for user " . $userName);
				$rqt->Fermer();
				$cnx->Fermer();
				throw new Exception($error->GetFunctionalLabel(), $error->getHttpStatus());
			}else{
				$rc = new Quota($rqt);
				$rc = $rc->toArray();
			}
			$rqt->Fermer();
		}else{
			if (isset($request_data["order"]) && $request_data["order"] != ""){
				$strSQL .= " ORDER BY " . $request_data["order"];
			}
			if (!$rqt->Ouvrir($strSQL, $cnx)){
				$error->setHttpStatus(500);
				$error->setFunctionalCode(3);
				$error->setFunctionalLabel($rqt->Erreur->GetTexte());
				$cnx->Fermer();
				throw new Exception($error->GetFunctionalLabel(), $error->getHttpStatus());				
			}
			$rc = Array();
			while (!$rqt->EOF()){
				$quota = new Quota($rqt);
				array_push($rc, $quota->toArray());
				$rqt->Suivant();
			}
				
			
			$rqt->Fermer();
		}
		$cnx->Fermer();
		return $rc;
}



function getUnsetQuotas($serviceName = NULL,  $request_data=NULL){
	GLOBAL $BDName;
	GLOBAL $BDUser;
	GLOBAL $BDPwd;

	$serviceName=normalizeName($serviceName);

	$error = new Error();


	$cnx = new Connexion();
	if (!$cnx->Ouvrir($BDName, $BDUser, $BDPwd)){
			$error->setHttpStatus(500);
			$error->setFunctionalCode(3);
			$error->setFunctionalLabel($cnx->GetErreur()->GetTexte());
			throw new Exception($error->GetFunctionalLabel(), $error->getHttpStatus());				
		
	}

	$rqt = new RequeteResultat();
	$strSQL="";
	$strSQL.="SELECT u.* ";
	$strSQL.="FROM 	users u, ";
	$strSQL.="	   	usersgroups ug, ";
	$strSQL.="		services s ";
	$strSQL.="WHERE isUserQuotasEnabled=1 ";
	$strSQL.="AND	s.groupName=ug.groupName ";
	$strSQL.="AND	ug.userName=u.userName ";
	$strSQL.="AND	s.serviceName='" . DoubleQuote($serviceName, SERVICENAME_LENGTH) . "' ";
	$strSQL.="AND	u.userName not in (SELECT uq.userName FROM usersquotas uq WHERE uq.serviceName='" . DOubleQuote($serviceName, SERVICENAME_LENGTH) . "') ";  
	if (!$rqt->Ouvrir($strSQL, $cnx)){
		$error->setHttpStatus(500);
		$error->setFunctionalCode(3);
		$error->setFunctionalLabel($rqt->Erreur->GetTexte());
		$cnx->Fermer();
		throw new Exception($error->GetFunctionalLabel(), $error->getHttpStatus());				
	}
	$rc = Array();
	while (!$rqt->EOF()){
		$user = new User($rqt);
		array_push($rc, $user->toArray());
		$rqt->Suivant();
	}
	$rqt->Fermer();
	$cnx->Fermer();
	return $rc;
}


function nodesListForService($serviceName, $request_data=NULL){
	GLOBAL $BDName;
	GLOBAL $BDUser;
	GLOBAL $BDPwd;

	$serviceName=normalizeName($serviceName);
	$error = new Error();


	$cnx = new Connexion();
	if (!$cnx->Ouvrir($BDName, $BDUser, $BDPwd)){
			$error->setHttpStatus(500);
			$error->setFunctionalCode(3);
			$error->setFunctionalLabel($cnx->GetErreur()->GetTexte());
			throw new Exception($error->GetFunctionalLabel(), $error->getHttpStatus());
		
	}
	if ($serviceName==NULL){
		$strSQL= "SELECT n.*, 0 as onNode FROM nodes n";
	}else{
		$strSQL= "SELECT n.*, exists(SELECT 'x' FROM servicesnodes sn WHERE sn.serviceName='" . DoubleQuote($serviceName) . "' and sn.nodeName=n.nodeName) as onNode FROM nodes n";
	}
	if (isset($request_data["order"])){
		$strSQL =$strSQL .  " ORDER BY " . $request_data["order"];
	}
	$rqt = new RequeteResultat();
	if (!$rqt->Ouvrir($strSQL, $cnx)){
		$error->setHttpStatus(500);
		$error->setFunctionalCode(3);
		$error->setFunctionalLabel($rqt->Erreur->GetTexte());
		$cnx->Fermer();
		throw new Exception($error->GetFunctionalLabel(), $error->getHttpStatus());				
	}
	$rc = Array();
	while (!$rqt->EOF()){
		$node = new Node($rqt);
		$published = $rqt->Champ("onNode");
		array_push($rc, Array ("node" => $node->toArray(),
							   "published" => $published));
		$rqt->Suivant();
	}
	$cnx->Fermer();
	return $rc;

}




function setNodesListForService($serviceName, $request_data=NULL){
	GLOBAL $BDName;
	GLOBAL $BDUser;
	GLOBAL $BDPwd;

	$serviceName=normalizeName($serviceName);
	$error = new Error();

	if (count($request_data) <=0){
		throw new RestException(400,"At least one node to publish on is required");
	}


	$cnx = new Connexion();
	if (!$cnx->Ouvrir($BDName, $BDUser, $BDPwd)){
			$error->setHttpStatus(500);
			$error->setFunctionalCode(3);
			$error->setFunctionalLabel($cnx->GetErreur()->GetTexte());
			throw new Exception($error->GetFunctionalLabel(), $error->getHttpStatus());
		
	}
	$rqt = new RequeteResultat();
	if (!$rqt->Executer("DELETE FROM servicesnodes WHERE serviceName='" . DoubleQuote($serviceName) . "'", $cnx)){
		$error->setHttpStatus(500);
		$error->setFunctionalCode(3);
		$error->setFunctionalLabel($rqt->Erreur->GetTexte());
		$cnx->Fermer();
		throw new Exception($error->GetFunctionalLabel(), $error->getHttpStatus());				
	}
	for ($i=0;$i<count($request_data) ;$i++){
		$strSQL = "INSERT INTO servicesnodes (serviceName, nodeName) VALUES ('" . DoubleQuote($serviceName) . "','" . DOubleQuote($request_data[$i]) . "')";
		if (!$rqt->Executer($strSQL, $cnx)){
			$error->setHttpStatus(500);
			$error->setFunctionalCode(3);
			$error->setFunctionalLabel($rqt->Erreur->GetTexte());
			$cnx->Fermer();
			throw new Exception($error->GetFunctionalLabel(), $error->getHttpStatus());				
		}
	}
	$cnx->Fermer();
	if (isset($request_data["noApply"])){
		return nodesListForService($serviceName);
	}else if (applyApacheConfiguration()){
		return nodesListForService($serviceName);
	}else{
		$error->setHttpStatus(500);
		$error->setFunctionalCode(1);
		$error->setFunctionalLabel("Service successfully saved but unable to apply configuration on runtime appliance");
		throw new Exception($error->GetFunctionalLabel(), $error->getHttpStatus());
	}
	
}