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
 * File Name   : ApplianceManager/ApplianceManager.php/js/quotas.js
 *
 * Created     : 2012-02
 * Authors     : Benoit HERARD <benoit.herard(at)orange.com>
 *
 * Description :
 *      AJAX Managegment for quotas
 *--------------------------------------------------------
 * History     :
 * 1.0.0 - 2012-10-01 : Release of the file
*/

var currentQuotaUri;
var quotaModified;

var userServiceQuotaToolTip="Available service on which user quotas are defined";



function saveNewQuota(){
	saveOrUpdateQuota('POST');
}


function updateQuota(){
	saveOrUpdateQuota('PUT');
}

function saveOrUpdateQuota(method){
	currentQuotaUri=encodeURIComponent(document.getElementById("quotaUri").value);
	reqSec = "reqSec=" + encodeURIComponent(document.getElementById("reqSec").value);
	reqDay = "reqDay=" + encodeURIComponent(document.getElementById("reqDay").value);
	reqMonth = "reqMonth=" + encodeURIComponent(document.getElementById("reqMonth").value);
	postData=reqSec + "&" + reqDay + "&" + reqMonth;
	$.ajax({
		  url: currentQuotaUri + "?" + postData, 
		  dataType: 'json',
		  type:method,
		  data: postData,
		  success: startDisplayUserQuotasForCurrentUser,
		  error: displayError
		});
	
	

}

function setQuotaModified(isModified){
	quotaModified=isModified;
	if (isModified){
		setActionButtonEnabled('saveEditQuotas',true);
	}else{
		setActionButtonEnabled('saveEditQuotas',false);
	}
}

function startEditUserQuotasForCurrentQuota(){
	startEditUserQuotas(currentQuotaUri);
}


function startEditUserQuotas(quotaURI){
	currentQuotaUri=quotaURI;
	$.getJSON(quotaURI, editUserQuotas).error(displayError);
}


function startPopulateUnsetQuotas(userURI){
	$.getJSON(userURI + "/quotas/unset/", populateUnsetQuotas).error(displayError);
}


function populateUnsetQuotas(quotaList){
	if (quotaList.length>0){
		strHTML="";
		strHTML+="<select title=\"" +  userServiceQuotaToolTip + "\" name=\"quotaUri\" id=\"quotaUri\" >";
		for (i=0;i<quotaList.length;i++){
			strHTML+="<option name=\"" + quotaList[i].serviceName + "\" value=\"" + quotaList[i].uri + "\"  >" + quotaList[i].serviceName + "</option>";
		}
		strHTML+="</select>";
		c=document.getElementById('unsetQuotas');
		c.innerHTML=strHTML;
	}
	
}

function addUserQuotas(){
	strHTML="";
	strHTML+="<center>";
	strHTML+="<h1>";
	strHTML+="Add quotas on service for user " +  currentUser.userName + " settings";
	strHTML+="</h1>";
	strHTML+="<hr>";
	strHTML+="<form>";
	strHTML+="<table class=\"tabular_table\">";
	strHTML+="	<tr class=\"tabular_table_body\">";
	strHTML+="		<th>Service:</th>";
	strHTML+="		<td><div id=\"unsetQuotas\"></div></td>";
	strHTML+="	<tr>";
	strHTML+="	<tr class=\"tabular_table_body\">";
	strHTML+="		<th>Maximun request per second:</th>";
	strHTML+="		<td><input class=\"inputNumber\"  title=\"" + perSecToolTip + "\" type=\"text\" id=\"reqSec\" value=\"\" onchange=\"setQuotaModified(true)\" onkeypress=\"setQuotaModified(true)\"></td>";
	strHTML+="	<tr>";
	strHTML+="	<tr class=\"tabular_table_body\">";
	strHTML+="		<th>Maximun request per day:</th>";
	strHTML+="		<td><input class=\"inputNumber\"   title=\"" + perDayToolTip + "\" type=\"text\" id=\"reqDay\" value=\"\" onchange=\"setQuotaModified(true)\" onkeypress=\"setQuotaModified(true)\"></td>";
	strHTML+="	<tr>";
	strHTML+="	<tr class=\"tabular_table_body\">";
	strHTML+="		<th>Maximun request per month:</th>";
	strHTML+="		<td><input class=\"inputNumber\"   title=\"" + perMonthToolTip + "\"  type=\"text\" id=\"reqMonth\" value=\"\" onchange=\"setQuotaModified(true)\" onkeypress=\"setQuotaModified(true)\"></td>";
	strHTML+="	<tr>";
	strHTML+="</table>";
	strHTML+="<br>";
	strHTML+="<input type=\"button\" id=\"saveEditQuotas\" onclick=\"saveNewQuota()\" value=\"Save\" class=\"button_orange\">&nbsp;";
	strHTML+="<input type=\"button\" id=\"cancelEditQuotas\" onclick=\"startDisplayUserQuotasForCurrentUser()\" value=\"Cancel\" class=\"button_orange\">&nbsp;";
	strHTML+="</form>";
	strHTML+="<hr>";
	
	strHTML+="</center>";
	
	c=document.getElementById('content');
	c.innerHTML=strHTML;
	setQuotaModified(true);
	startPopulateUnsetQuotas(currentUserUri);
}


function editUserQuotas(quota){
		strHTML="";
		strHTML+="<center>";
		strHTML+="<h1>";
		strHTML+="Quotas on service " + quota.serviceName + " for user " +  quota.userName + " settings";
		strHTML+="</h1>";
		strHTML+="<hr>";
		strHTML+="<form>";
		strHTML+="<input type=\"hidden\" id=\"quotaUri\" value=\"" + quota.uri + "\">";
		strHTML+="<table class=\"tabular_table\">";
		strHTML+="	<tr class=\"tabular_table_body\">";
		strHTML+="		<th>Maximun request per second:</th>";
		strHTML+="		<td><input  class=\"inputNumber\"   title=\"" + perSecToolTip + "\"  type=\"text\" id=\"reqSec\" value=\"" + quota.reqSec + "\" onchange=\"setQuotaModified(true)\" onkeypress=\"setQuotaModified(true)\"></td>";
		strHTML+="	<tr>";
		strHTML+="	<tr class=\"tabular_table_body\">";
		strHTML+="		<th>Maximun request per day:</th>";
		strHTML+="		<td><input   class=\"inputNumber\"  title=\"" + perDayToolTip + "\"  type=\"text\" id=\"reqDay\" value=\"" + quota.reqDay + "\" onchange=\"setQuotaModified(true)\" onkeypress=\"setQuotaModified(true)\"></td>";
		strHTML+="	<tr>";
		strHTML+="	<tr class=\"tabular_table_body\">";
		strHTML+="		<th>Maximun request per month:</th>";
		strHTML+="		<td><input  class=\"inputNumber\"   title=\"" + perMonthToolTip + "\"  type=\"text\" id=\"reqMonth\" value=\"" + quota.reqMonth + "\" onchange=\"setQuotaModified(true)\" onkeypress=\"setQuotaModified(true)\"></td>";
		strHTML+="	<tr>";
		strHTML+="</table>";
		strHTML+="<br>";
		strHTML+="<input type=\"button\" id=\"saveEditQuotas\" onclick=\"updateQuota('" + quota.uri + "')\" value=\"Save\" class=\"button_orange\">&nbsp;";
		strHTML+="<input type=\"button\" id=\"cancelEditQuotas\" onclick=\"startDisplayUserQuotasForCurrentUser()\" value=\"Cancel\" class=\"button_orange\">&nbsp;";
		strHTML+="</form>";
		strHTML+="<hr>";
		
		strHTML+="</center>";
		
		c=document.getElementById('content');
		c.innerHTML=strHTML;
		currentQuotaUri=quota.uri;
		setQuotaModified(false);
}
	