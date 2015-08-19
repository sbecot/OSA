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
 * File Name   : ApplianceManager/ApplianceManager.php/js/counters.js
 *
 * Created     : 2012-02
 * Authors     : Benoit HERARD <benoit.herard(at)orange.com>
 *
 * Description :
 *      AJAX Management for counters
 *--------------------------------------------------------
 * History     :
 * 1.0.0 - 2012-10-01 : Release of the file
*/

			var currentCounter;
			var counterModified;

			var counterSearch_resourceName;
			var counterSearch_userName;
			var counterSearch_timeUnit;

			var currentCounterList;
			
			
			var resourceNameToolTip="Choose resource on which you want to search counters.";
			var userNameToolTip="Choose user for which you want to search counters.";
			var timeUnitToolTip="Choose the time unit (second/day/month) for which you want to search counters.";
			var counterValueToolTip="Set the value for this counter.";
			var editCounterToolTip="Edit this counter";
			var deleteCounterToolTip="Delete this counter";

			var backList;
			
			function setCounterModified(isModified){
				counterModified=isModified;
				if (isModified){
					setActionButtonEnabled('save',true);
				}else{
					setActionButtonEnabled('save',false);
				}
			}
			
			

			function updateCounter(counterURI) {
				saveOrUpdateCounter('PUT');
			}

			function saveOrUpdateCounter(method){
				//currentCounterUri="counters/" + encodeURIComponent(currentCounterURI);
				value = "value=" + encodeURIComponent(document.getElementById("counterValue").value);
				$.ajax({
					  url: currentCounterUri,  
					  dataType: 'json',
					  type:method,
					  data: value,
					  success: editCurrentCounter,
					  error: displayErrorV2
					});
				
				

			}
			
			
			function startEditCounter(counterNum){
				
				currentCounterUri=currentCounterList[counterNum].uri;
				editCurrentCounter();
			}
			function editCurrentCounter(){
				$.getJSON(currentCounterUri, editCounter).error(displayErrorV2);
			}

			
			
			
			
			
			function generateCounterProperties(counter){
				if (counter != null){
					userName=counter.userName;
					resourceName=counter.resourceName;
					timeUnit=counter.timeUnit;
					timeValue = counter.timeValue;
					value=counter.value;
					counterUri=counter.uri;
				}else{
					userName="";
					resourceName="";
					timeUnit="";
					timeValue = "";
					value="";
					counterUri="";
				}
				strHTML="";
				strHTML+="<table class=\"tabular_table\">";
				strHTML+="<tr class=\"tabular_table_body\">";
				strHTML+="<th>Service Name:</th>";
				strHTML+="<td>" + resourceName + "</td>";
				strHTML+="<tr class=\"tabular_table_body\">";
				strHTML+="<th>User name:</th>";
				strHTML+="<td>" + userName  + "</td>";
				strHTML+="</tr>";
				strHTML+="<tr class=\"tabular_table_body\">";
				strHTML+="<th>time unit:</th>";
				strHTML+="<td>"+ timeUnit + "</td>";
				strHTML+="</tr>";
				strHTML+="<tr class=\"tabular_table_body\">";
				strHTML+="<th>time value:</th>";
				strHTML+="<td>" + timeValue + "</td>";
				strHTML+="</tr>";
				strHTML+="<tr class=\"tabular_table_body\">";
				strHTML+="<th>Value:</th>";
				strHTML+="<td><input class=\"inputText\" title=\"" + counterValueToolTip + "\" type='text' id='counterValue' value=\"" + value + "\"  onchange=\"setCounterModified(true)\"  onkeypress=\"setCounterModified(true)\"></td>";
				strHTML+="</tr>";
				strHTML+="</table>";
				setCounterModified(false);
				return strHTML;
			}
			
			function editCounter(counter){
				strHTMLForm="";
				strHTMLForm+="<center>";
				strHTMLForm+="<h1>";
				strHTMLForm+="Counter properties*";
				strHTMLForm+="</h1>";
				strHTMLForm+="<hr>";
				strHTMLForm+=generateCounterProperties(counter);
				strHTMLForm+="<br>";
				strHTMLForm+="<input type=\"button\" id=\"save\" onclick=\"updateCounter('" + counter.uri + "')\" value=\"Save\" class=\"button_orange\">&nbsp;";
				strHTMLForm+="<input type=\"button\" id=\"cancel\" onclick=\"backList()\" value=\"Done\" class=\"button_orange\">&nbsp;";
				strHTMLForm+="</form>";
				strHTMLForm+="<hr>";
				
				strHTMLForm+="</center>";

				c=document.getElementById('content');
				c.innerHTML=strHTMLForm;
				currentCounter=counter;
				currentCounterUi=counter.uri;
				setCounterModified(false);
			}

			
			
			function displayCounterList(counterList){
				
				
				strHTML="";
				strHTML+="<center>";
				strHTML+="<h1>";
				strHTML+=counterList.length + " counters found";
				strHTML+="</h1>";
				strHTML+="<hr>";
				if (counterList.length>0){
					strHTML+="<table id=\"countersList\" class=\"tabular_table scroll\">";
					strHTML+="<thead><tr class=\"tabular_table_header\">";
					strHTML+="<th>Service name</th>";
					strHTML+="<th>User name</th>";
					strHTML+="<th>Time unit</th>";
					strHTML+="<th>Time value</th>";
					strHTML+="<th>Value</th>";
					strHTML+="<th>Actions</th>";
					strHTML+="</tr></thead><tbody>";
					for (i=0;i<counterList.length;i++){
						strHTML+="<tr class=\"tabular_table_body" +  (i%2) + " item\">";
						strHTML+="<td class=\"serviceName\">" + counterList[i].resourceName + "</td>";
						strHTML+="<td class=\"userName\">" + counterList[i].userName + "</td>";
						strHTML+="<td class=\"timeUnit\">" + counterList[i].timeUnit + "</td>";
						strHTML+="<td class=\"timeValue\">" + counterList[i].timeValue + "</td>";
						strHTML+="<td class=\"counterValue\" >" + counterList[i].value + "</td>";
						strHTML+="<td class=\"action\">";
						strHTML+="<a  title=\"" + editCounterToolTip + "\" href=\"javascript:startEditCounter('" + i + "')\"><img border=\"0\" src=\"images/edit.gif\"></a><a title=\"" + deleteCounterToolTip + "\"  href=\"javascript:deleteCounter('" + i + "')\"><img border=\"0\" src=\"images/delete.gif\"></a>";
						strHTML+="</td>";
						strHTML+="</tr>";
					}
					strHTML+="</tbody>";
					strHTML+="</table>";
				}
				strHTML+="<br>";
				strHTML+="<input type=\"button\" id=\"refresh\" onclick=\"executeSearch()\" value=\"Refresh\" class=\"button_orange\">&nbsp;";
				strHTML+="</center>";

				c=document.getElementById('content');
				c.innerHTML=strHTML;
				currentCounterList=counterList;
				backList=executeSearch;
				$("table.scroll").createScrollableTable({
					width: '800px',
					height: '350px',
					border: '0px'
				});
				touchScroll("countersList_body_wrap");

			}

			function resetExceededCountersFilter(){
				$('#serviceNameFilter').val("");
				$('#userNameFilter').val("");
				searchExcedeedCounters();
				
			}

			function displayExcedeedCounterList(counterList){
				
				
				strHTML="";
				strHTML+="<center>";
				strHTML+="<h1>";
				strHTML+=counterList.length + " counters found"
				strHTML+="</h1>";
				strHTML+="<hr>";
				strHTML+= "<form onkeypress=\"return handelExcedeedCountersFilterFormKeypress(event)\">";
				strHTML+= "<table class=\"tabular_table searchFormTable\" id=\"countersEceeded\">";	
				strHTML+= "	<tr class=\"tabular_table_body\">";	
				strHTML+= "		<th>service name</th> <td><input type=\"text\" id=\"serviceNameFilter\" value=\"" + serviceNameFilterPrevVal + "\"></td>";	
				strHTML+= "		<th>user name</th> <td><input type=\"text\" id=\"userNameFilter\" value=\"" + userNameFilterPrevVal + "\"></td>";	
				strHTML+= "		<td><input type=\"button\" class=\"button_orange\" value=\"filter\" onclick=\"searchExcedeedCounters()\"><input type=\"button\" class=\"button_white\" value=\"reset\" onclick=\"resetExceededCountersFilter()\"></th> ";	
				strHTML+= "	</tr>";	
				strHTML+= "</table>";	
				strHTML+= "</form>";	
				strHTML+="<table class=\"tabular_table scroll\">";
				strHTML+="<tr class=\"tabular_table_header\">";
				strHTML+="<th>Service name</th>";
				strHTML+="<th>User name</th>";
				strHTML+="<th>Time unit</th>";
				strHTML+="<th>Time value</th>";
				strHTML+="<th>Value</th>";
				strHTML+="<th>Max allowed value</th>";
				strHTML+="<th>Actions</th>";
				strHTML+="</tr>";
				for (i=0;i<counterList.length;i++){
					strHTML+="<tr class=\"tabular_table_body" +  (i%2) + "\">";
					strHTML+="<td>" + counterList[i].resourceName + "</td>";
					strHTML+="<td>" + counterList[i].userName + "</td>";
					strHTML+="<td>" + counterList[i].timeUnit + "</td>";
					strHTML+="<td>" + counterList[i].timeValue + "</td>";
					strHTML+="<td>" + counterList[i].value + "</td>";
					strHTML+="<td>" + counterList[i].maxValue + "</td>";
					strHTML+="<td class=\"action\">";
					strHTML+="<a  title=\"" + editCounterToolTip + "\" href=\"javascript:startEditCounter('" + i + "')\"><img border=\"0\" src=\"images/edit.gif\"></a><a title=\"" + deleteCounterToolTip + "\"  href=\"javascript:deleteCounter('" + i + "')\"><img border=\"0\" src=\"images/delete.gif\"></a>";
					strHTML+="</td>";
				}
				strHTML+="</tr>";
				strHTML+="</table>";
				strHTML+="<br>";
				strHTML+="<input type=\"button\" id=\"refresh\" onclick=\"searchExcedeedCounters()\" value=\"Refresh\" class=\"button_orange\">&nbsp;";
				strHTML+="</center>";

				c=document.getElementById('content');
				c.innerHTML=strHTML;
				currentCounterList=counterList;
				backList=searchExcedeedCounters;
				$("table.scroll").createScrollableTable({
					width: '800px',
					height: '350px',
					border: '0px'
				});
			}

			
			function deleteCounter(counterNum){
				
				
				if (confirm("Are you sure to want to remove this counter?")){
					$.ajax({
						  url: currentCounterList[counterNum].uri,
						  dataType: 'json',
						  type:'DELETE',
						  //data: data,
						  success: backList,
						  error: displayErrorV2
						});
				}
				
			}
			
			
			function showCounters(){
				/*$.ajax({
					  url: './counter/',
					  dataType: 'json',
					  //data: data,
					  success: displayCounterList,
					  error: displayErrorV2
					});*/

				$.getJSON("./counters/?order=counterName", displayCounterList).error(displayErrorV2);
			}

			function startPopulateServices(){
				$.getJSON("services/?withQuotas&order=serviceName", populateServices).error(displayErrorV2);
			}
			function startPopulateUsers(){
				$.getJSON("users/?order=userName", populateUsers).error(displayErrorV2);
			}
			function populateServices(servicesList){
				if (servicesList.length>0){
					var serviceListAutoComplete=new Array();
					var autoCompIdx=0;
					for (i=0;i<servicesList.length;i++){
						if (servicesList[i].isHitLoggingEnabled==1){
							serviceListAutoComplete[autoCompIdx++]=servicesList[i].serviceName;
						}
					}

					$( "#resourceName" ).autocomplete({
									source: serviceListAutoComplete,
									minLength: 0
					});
				}
				
			}
			function populateUsers(usersList){
				
				if (usersList.length>0){
					var usersListAutoComplete=new Array();
					var autoCompIdx=0;
					for (i=0;i<usersList.length;i++){
						usersListAutoComplete[autoCompIdx++]=usersList[i].userName;
					}

					$( "#userName" ).autocomplete({
									source: usersListAutoComplete,
									minLength: 0
					});
				}
				
			}
			

			function startSearchCounters(){
				counterSearch_userName=document.getElementById("userName").value;
				counterSearch_resourceName=document.getElementById("resourceName").value;
				counterSearch_timeUnit=document.getElementById("timeUnit").value;

				executeSearch();
			}
			
			function handelExcedeedCountersFilterFormKeypress(e) {
				if (e.keyCode == 13) {
					searchExcedeedCounters();
					return false;
				}
			}					
			function searchExcedeedCounters(){
				prms="";
				prms=prms + "userNameFilter=" + encodeURIComponent(getFilterValue('userNameFilter'));
				prms=prms + "&resourceNameFilter=" + encodeURIComponent(getFilterValue('serviceNameFilter'));
				$.ajax({
					  url: "counters/excedeed/",
					  dataType: 'json',
					  type:'GET',
					  data: prms,
					  success: displayExcedeedCounterList,
					  error: displayErrorV2
					});
			}

			function executeSearch(){
				queryString="";
				if (counterSearch_resourceName != "All"){
					queryString+="resourceName=" + encodeURIComponent(counterSearch_resourceName) + "&";
				}
				
				if (counterSearch_userName == "None"){
					queryString+="userName=" ;
				}else if (counterSearch_userName != "All"){
					queryString+="userName=" + encodeURIComponent(counterSearch_userName) ;
				}
				
				if (counterSearch_timeUnit != "All"){
					if (queryString != ""){
						queryString+="&";
					}
					queryString+="timeUnit=" + encodeURIComponent(counterSearch_timeUnit) ;
				}
				$.ajax({
					  url: "counters/?" + queryString,
					  dataType: 'json',
					  type:'GET',
					  success: displayCounterList,
					  error: displayErrorV2
					});
				
			}
			
			function searchCounters(){
				strHTML="";
				strHTML+="<center>";
				strHTML+="<h1>";
				strHTML+="Search for counters";
				strHTML+="</h1>";
				strHTML+="<hr>";
				strHTML+="<table class=\"tabular_table\">";
				strHTML+="<tr class=\"tabular_table_body\">";
				strHTML+="<th>Resource Name:</th>";
				//<div id=\"resourceList\">
				strHTML+="<td><input class=\"inputText\" title=\"" + resourceNameToolTip + "\" type\"text\" id=\"resourceName\" onfocus=\"javascript:$(this).autocomplete('search',$(this).value);\"></td>";
				strHTML+="<tr class=\"tabular_table_body\">";
				strHTML+="<th>User name:</th>";
				strHTML+="<td><input class=\"inputText\" title=\"" + userNameToolTip + "\" type\"text\" id=\"userName\" onfocus=\"javascript:$(this).autocomplete('search',$(this).value);\"></td>";
				strHTML+="</tr>";
				strHTML+="<tr class=\"tabular_table_body\">";
				strHTML+="<th>time unit:</th>";
				strHTML+="<td>";
				strHTML+="	<select  class=\"inputText\"  title=\"" + timeUnitToolTip + "\"  id=\"timeUnit\" name=\"timeUnit\">" ;
				strHTML+="		<option value=\"All\">All</option>" ;
				strHTML+="		<option value=\"M\">Month</option>" ;
				strHTML+="		<option value=\"D\">Day</option>" ;
				strHTML+="		<option value=\"S\">Second</option>" ;
				strHTML+="	</select>" ;
				strHTML+="</td>";
				strHTML+="</tr>";
				strHTML+="</table>";
				strHTML+="<br>";
				strHTML+="<input type=\"button\" id=\"seach\" onclick=\"startSearchCounters()\" value=\"Search\" class=\"button_orange\">&nbsp;";
				strHTML+="</form>";
				strHTML+="<hr>";
				strHTML+="</center>";
				
				
				

				
				startPopulateServices();
				startPopulateUsers();
				c=document.getElementById('content');
				c.innerHTML=strHTML;
				
				
			}
			
//Event 			
			$(
				function (){
					$('#searchCounter').click(searchCounters);
					$('#searchExcedeedCounters').click(searchExcedeedCounters);
				}
			)