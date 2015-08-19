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
 * File Name   : ApplianceManager/ApplianceManager.php/js/users.js
 *
 * Created     : 2012-02
 * Authors     : Benoit HERARD <benoit.herard(at)orange.com>
 *
 * Description :
 *      AJAX MAnagement for users
 *--------------------------------------------------------
 * History     :
 * 1.0.0 - 2012-10-01 : Release of the file
 */

var currentUserURI;
var currentUser;
var userModified;

var identityForwardingComplement = "\nIf identity forwarding is activated on a service,<br>this information may be forwarded to correspondant backends";

var userNameToolTip = "User name is the login name used by a enduser to call protected services on publishing nodes."
		+ identityForwardingComplement;
var passwordToolTip = "End user password to call protected service on publishing nodes";
var firstNameToolTip = "End user first name (Optional)"
		+ identityForwardingComplement;
var lastNameToolTip = "End user last name (Optional)"
		+ identityForwardingComplement;
var entityNameToolTip = "End user entity (Optional)"
		+ identityForwardingComplement;
var mailNameToolTip = "End user email"
		+ identityForwardingComplement;
var userExtraToolTip = "Any extra data at your choice (without \\n or \\n)"
		+ identityForwardingComplement;
var endDateNameToolTip = "From this date, user will not be alloed to connect publishing nodes anymore";

var addUserToolTip = "Add a new user to the system";
var editUserToolTip = "Edit this user (including its groups and quotas)";
var deleteUserToolTip = "Delete this user";
var editUserGroupsToolTip = "Edit the group membership for this user";
var editUserQuotasToolTip = "Define quotas for services requiring end users quotas management for this user";

var availableGroupsToolTip = "List of available groups for which current user is not member<br>Multi selection is possible";
var deleteUserGroupToolTip = "Remove current user for this group";
var addAllGroupsTooltip = "Add user as member to selected groups";

var editQuota = "Edit this quota";
var deleteQuota = "Delete this quota";


var userNameFilterPrevVal="";
var firstNameFilterPrevVal="";
var lastNameFilterPrevVal="";
var emailAddressFilterPrevVal="";
var entityFilterPrevVal="";



function deleteUserQuotas(quotaURI, serviceName) {
	if (confirm("Are you sure to want to delete quotas on service "
			+ serviceName + " for this user?")) {
		$.ajax({
			url : quotaURI,
			dataType : 'json',
			type : 'DELETE',
			// data: data,
			success : startDisplayUserQuotasForCurrentUser,
			error : displayErrorV2
		});
	}

}

function setUserModified(isModified) {
	userModified = isModified;
	if (isModified) {
		setActionButtonEnabled('saveNew', true);
		setActionButtonEnabled('saveEdit', true);
		setActionButtonEnabled('groupsEdit', false);
		setActionButtonEnabled('quotasEdit', false);
	} else {
		setActionButtonEnabled('saveNew', false);
		setActionButtonEnabled('saveEdit', false);
		setActionButtonEnabled('groupsEdit', true);
		setActionButtonEnabled('quotasEdit', true);
	}
}

function deleteUserGroup(groupURI, groupName, userURI) {
	if (confirm("Are you sure to want to remove group " + groupName
			+ " for this user?")) {
		$.ajax({
			url : userURI + "/" + groupURI,
			dataType : 'json',
			type : 'DELETE',
			// data: data,
			success : startDisplayUserGroupsForCurrentUser,
			error : displayErrorV2
		});
	}

}

function addGroupToUser(userURI) {
	grps = document.getElementById('availableGroupsList');
	selectedCount = 0;
	// count selected item to be able to start reload page on last one
	for (i = 0; i < grps.options.length; i++) {
		if (grps.options[i].selected) {
			selectedCount++;
		}
	}
	currentItem = 1;
	for (i = 0; i < grps.options.length; i++) {
		if (grps.options[i].selected) {
			if (currentItem == selectedCount) {
				onSuccess = startDisplayUserGroupsForCurrentUser;
			} else {
				onSusccess = null;
			}
			$.ajax({
				url : userURI + "/" + grps.options[i].value,
				dataType : 'json',
				type : 'POST',
				data : null,
				success : startDisplayUserGroupsForCurrentUser,
				error : displayErrorV2
			});
			currentItem++;
		}
	}
	startDisplayUserGroups(userURI);
}

function addUser() {
	strHTML = "";
	strHTML += "<center>";
	strHTML += "<h1>";
	strHTML += "New User properties";
	strHTML += "</h1>";
	strHTML += "<hr>";
	strHTML += "<form>";
	strHTML += generateUserProperties(null);
	strHTML += "";
	strHTML += "<br>";
	strHTML += "<input type=\"button\" id=\"saveNew\" onclick=\"saveNewUser()\" value=\"Save\" class=\"button_orange\">&nbsp;";
	strHTML += "<input type=\"button\" id=\"cancelNew\" onclick=\"showUsers()\" value=\"Done\" class=\"button_orange\">";
	strHTML += "</form>";
	strHTML += "</center>";

	c = document.getElementById('content');
	c.innerHTML = strHTML;
	$('#userEndDate').datepicker();
	setUserModified(false);
}
function saveNewUser() {
	saveOrUpdateUser('POST');
}

function updateUser(userURI) {
	saveOrUpdateUser('PUT');
}

function saveOrUpdateUser(method) {
	currentUserUri = "users/"
			+ encodeURIComponent(document.getElementById("userName").value);
	password = "password="
			+ encodeURIComponent(document.getElementById("userPass").value);
	userName = "userName="
			+ encodeURIComponent(document.getElementById("userName").value);
	email = "email=" + encodeURIComponent(document.getElementById("userMail").value);
	firstName = "firstName="
			+ encodeURIComponent(document.getElementById("firstName").value);
	lastName = "lastName="
			+ encodeURIComponent(document.getElementById("lastName").value);
	entity = "entity=" + encodeURIComponent(document.getElementById("entity").value);
	extra = "extra=" + encodeURIComponent(document.getElementById("extra").value);
	try{
		endDate = "endDate="
				+ encodeURIComponent(new Date(document.getElementById("userEndDate").value)
						.format("isoUtcDateTime"));
	}catch (ex){
		endDate="endDate=";
	}
	postData = password + "&" + email + "&" + endDate + "&" + firstName + "&"
			+ lastName + "&" + entity + "&" + extra;
	if (method == 'POST') {
		uri = "users/";
		postData = "userName="
				+ encodeURIComponent(document.getElementById("userName").value) + "&"
				+ postData;
	} else {
		uri = "users/" + encodeURIComponent(document.getElementById("userName").value);
	}
	$.ajax({
		url : uri, // "users/" +
					// encodeURIComponent(document.getElementById("userName").value) ,
					// //+ "?" + postData,
		dataType : 'json',
		type : method,
		data : postData,
		success : startEditCurrentUser,
		error : displayErrorV2
	});

}
function startEditUser(userURI) {
	currentUserUri = userURI;
	$.getJSON(userURI, editUser).error(displayErrorV2);
}
function startEditCurrentUser() {
	startEditUser(currentUserUri);
}

function startDisplayAvailableGroups(userURI) {
	currentUserURI = userURI;
	$.getJSON(userURI + "/groups/available/", displayAvailableGroups).error(
			displayErrorV2);
}

function startDisplayUserGroupsForCurrentUser(group) {
	startDisplayUserGroups(currentUserURI);
}
function startDisplayUserGroups(userURI) {
	currentUserURI = userURI;
	$.getJSON(userURI + "/groups/", displayUserGroups).error(displayErrorV2);
}
function startDisplayUserQuotas(userURI) {
	currentUserURI = userURI;
	$.getJSON(userURI + "/quotas/", displayUserQuotas).error(displayErrorV2);
}
function startDisplayUserQuotasForCurrentUser() {
	startDisplayUserQuotas(currentUserUri);
}

function displayAvailableGroups(groupList) {
		strHTML = "";
		strHTML += "<center><select title=\""
				+ availableGroupsToolTip
				+ "\" name=\"availableGroupsList\" id=\"availableGroupsList\" size=\"15\" multiple  class=\"availableGroupsList\">";
		for (i = 0; i < groupList.length; i++) {
			strHTML += "<option name=\"" + groupList[i].groupName
					+ "\" value=\"groups/" + groupList[i].groupName + "\">"
					+ groupList[i].groupName + "</option>";
		}
		strHTML += "</select><br><br>";
		if (groupList.length > 0) {
			strHTML += "<input  title=\""
					+ addAllGroupsTooltip
					+ "\" type=\"button\" id=\"addGroups\" onclick=\"addGroupToUser('"
					+ currentUserURI
					+ "')\" value=\"<<\" class=\"button_orange\">&nbsp;</center>";
		}
		c = document.getElementById('availableGroups');
		c.innerHTML = strHTML;
}

function generateUserProperties(user) {
	if (user != null) {
		userName = user.userName;
		password = user.password;
		emailAddress = user.emailAddress;
		userDate = new Date();
		userDate.setISO8601(user.endDate);
		firstName = user.firstName;
		lastName = user.lastName;
		entity = user.entity;
		extra = user.extra==null?"":user.extra;
		dateFormated = userDate.format("mm/dd/yyyy");
	} else {
		userName = "";
		password = "";
		emailAddress = "";
		dateFormated = "";
		firstName = "";
		lastName = "";
		entity = "";
		extra = "";
	}
	strHTML = "";
	strHTML += "<table class=\"tabular_table\">";
	strHTML += "<tr class=\"tabular_table_body\">";
	strHTML += "<th>User name:</th>";
	if (user != null) {
		strHTML += "<td><input type=\"hidden\" id=\"userName\" value=\""
				+ userName + "\">" + userName + "</td>";
	} else {
		strHTML += "<td><input class=\"inputText\" title=\""
				+ userNameToolTip
				+ "\"  type=\"text\" id=\"userName\" value=\"\"  onchange=\"setUserModified(true)\" onkeypress=\"setUserModified(true)\"></td>";
	}
	strHTML += "<tr class=\"tabular_table_body\">";
	strHTML += "<th>Password:</th>";
	strHTML += "<td><input class=\"inputText\"  title=\""
			+ passwordToolTip
			+ "\" type='password' id='userPass' value=\""
			+ password
			+ "\" onchange=\"setUserModified(true)\"  onkeypress=\"setUserModified(true)\"></td>";
	strHTML += "</tr>";
	strHTML += "<tr class=\"tabular_table_body\">";
	strHTML += "<th>Firstname:</th>";
	strHTML += "<td><input class=\"inputText\"  title=\""
			+ firstNameToolTip
			+ "\" type='text' id='firstName' value=\""
			+ firstName
			+ "\"  onchange=\"setUserModified(true)\"  onkeypress=\"setUserModified(true)\"></td>";
	strHTML += "</tr>";
	strHTML += "<tr class=\"tabular_table_body\">";
	strHTML += "<th>Last name:</th>";
	strHTML += "<td><input class=\"inputText\" title=\""
			+ lastNameToolTip
			+ "\"  type='text' id='lastName' value=\""
			+ lastName
			+ "\"  onchange=\"setUserModified(true)\"  onkeypress=\"setUserModified(true)\"></td>";
	strHTML += "</tr>";
	strHTML += "<tr class=\"tabular_table_body\">";
	strHTML += "<th>Entity:</th>";
	strHTML += "<td><input class=\"inputText\"  title=\""
			+ entityNameToolTip
			+ "\" type='text' id='entity' value=\""
			+ entity
			+ "\"  onchange=\"setUserModified(true)\"  onkeypress=\"setUserModified(true)\"></td>";
	strHTML += "</tr>";
	strHTML += "<tr class=\"tabular_table_body\">";
	strHTML += "<th>Email address:</th>";
	strHTML += "<td><input class=\"inputText\"  title=\""
			+ mailNameToolTip
			+ "\"  type='text' id='userMail' value=\""
			+ emailAddress
			+ "\"  onchange=\"setUserModified(true)\"  onkeypress=\"setUserModified(true)\"></td>";
	strHTML += "</tr>";
	strHTML += "<tr class=\"tabular_table_body\">";
	strHTML += "<th>End date:</th>";
	strHTML += "<td>";
	strHTML += "	<div>";
	strHTML += "	 	<input class=\"inputDT\"  title=\""
			+ endDateNameToolTip
			+ "\" type=\"text\" name=\"userEndDate\" id=\"userEndDate\" value=\""
			+ dateFormated + "\" onchange=\"setUserModified(true)\"/>";
	strHTML += "	 </div>";
	strHTML += "</tr>";
	strHTML+="			<tr class=\"tabular_table_body\">\n";
	strHTML+="				<th>Addtional data:</th>\n";
	strHTML+="				<td><textarea rows=\"10\"  title=\"" + userExtraToolTip + "\"   id=\"extra\" onClick=\"setUserModified(true)\"  onchange=\"setUserModified(true)\" onkeypress=\"setUserModified(true)\">" + extra + "</textarea></td>\n";
	strHTML+="				</td>\n";
	strHTML+="			</tr>\n";
	strHTML += "</table>";
	setUserModified(false);
	return strHTML;
}

function editUser(user) {
	userDate = new Date(user.endDate);
	strHTML = "";
	strHTML += "<center>";
	strHTML += "<h1>";
	strHTML += "User " + user.userName + " properties";
	strHTML += "</h1>";
	strHTML += "<hr>";
	strHTML += generateUserProperties(user);
	strHTML += "<br>";
	strHTML += "<input type=\"button\" id=\"saveEdit\" onclick=\"updateUser('"
			+ user.uri + "')\" value=\"Save\" class=\"button_orange\">&nbsp;";
	strHTML += "<input type=\"button\" id=\"cancelEdit\" onclick=\"showUsers()\" value=\"Done\" class=\"button_orange\">&nbsp;";
	strHTML += "<input type=\"button\"  title=\"" + editUserGroupsToolTip
			+ "\"  id=\"groupsEdit\"onclick=\"startDisplayUserGroups('"
			+ user.uri + "')\" value=\"Groups\" class=\"button_orange\">&nbsp;";
	strHTML += "<input type=\"button\"  title=\"" + editUserQuotasToolTip
			+ "\" id=\"quotasEdit\"onclick=\"startDisplayUserQuotas('"
			+ user.uri + "')\" value=\"Quotas\" class=\"button_orange\">";
	strHTML += "<div id=\"userGroups\"\>"
	strHTML += "</form>";
	strHTML += "<hr>";

	strHTML += "</center>";

	c = document.getElementById('content');
	c.innerHTML = strHTML;
	currentUser = user;
	currentUserUi = user.uri;
	$('#userEndDate').datepicker();
	setUserModified(false);

}

function displayUserGroups(groupList) {

	strHTML = "";
	strHTML += "<center>";
	strHTML += "<h2>";
	strHTML += currentUser.userName + "'s groups ";
	strHTML += "</h2>";
	strHTML += "<table list=\"groupsList\" class=\"tabular_table\">";
	strHTML += "	<tr class=\"tabular_table_body\">";
	strHTML += "		<th>Membership</th>";
	strHTML += "		<th>Available for membership</th>";
	strHTML += "	</tr>";
	strHTML += "	<tr class=\"tabular_table_body\">";
	strHTML += "		<td valign=top>";
	if (groupList.length > 0) {
		strHTML += "			<table class=\"tabular_table scroll\">";
		strHTML += "				<thead>";
		strHTML += "				<tr class=\"tabular_table_header\">";
		strHTML += "					<th>Groupname</th>";
		strHTML += "					<th>Description</th>";
		strHTML += "					<th>Actions</th>";
		strHTML += "				</tr>";
		strHTML += "				</thead></tbody>";
		for (i = 0; i < groupList.length; i++) {
			strHTML += "			<tr class=\"tabular_table_body" + (i % 2) + "\">";
			strHTML += "				<td>" + groupList[i].groupName + "</td>";
			strHTML += "				<td>" + groupList[i].description + "</td>";
			strHTML += "				<td>";
			if ( groupList[i].groupName != "Admin" || currentUser.userName != "Admin"){
				strHTML += "					<a  title=\"" + deleteUserGroupToolTip + "\" href=\"javascript:deleteUserGroup('" + "groups/" + groupList[i].groupName + "', '" + groupList[i].groupName  + "',  '" + currentUserURI + "')\"><img border=\"0\" src=\"images/delete.gif\"></a>";
			}
			strHTML += "				</td>";
			strHTML += "			</tr>";
		}
		strHTML += "			</tbody>";
		strHTML += "			</table>";
	} else {
		strHTML += "			&nbsp;";
	}
	strHTML += "		</td>";
	strHTML += "		<td valign=top>";
	strHTML += "			<div id=\"availableGroups\"/>";
	strHTML += "		</td>";
	strHTML += "	<tr>";
	strHTML += "</table>";
	strHTML += "<br>";
	strHTML += "<input type=\"button\" onclick=\"startEditUser('"
			+ currentUser.uri
			+ "')\" value=\"Done\" class=\"button_orange\">&nbsp;";
	strHTML += "<form>";
	strHTML += "</center>";

	// c=document.getElementById('userGroups');
	c = document.getElementById('content');
	c.innerHTML = strHTML;
	startDisplayAvailableGroups(currentUserURI);
	$("table.scroll").createScrollableTable({
		width: '350px',
		height: '200px',
		border: '0px'
	});
	touchScroll("groupsList_body_wrap");
}

function displayUserQuotas(quotasList) {

	strHTML = "";
	strHTML += "<center>";
	strHTML += "<h2>";
	strHTML += currentUser.userName + "'s quotas ";
	strHTML += "</h2>";
	if (quotasList.length > 0) {
		strHTML += "<table class=\"tabular_table\">";
		strHTML += "	<tr class=\"tabular_table_header\">";
		strHTML += "		<th>Servicename</th>";
		strHTML += "		<th>Max/sec</th>";
		strHTML += "		<th>Max/day</th>";
		strHTML += "		<th>Max/month</th>";
		strHTML += "		<th>Actions</th>";
		strHTML += "</tr>";
		for (i = 0; i < quotasList.length; i++) {
			strHTML += "<tr class=\"tabular_table_body" + (i % 2) + "\">";
			strHTML += "	<td>" + quotasList[i].serviceName + "</td>";
			strHTML += "	<td>" + quotasList[i].reqSec + "</td>";
			strHTML += "	<td>" + quotasList[i].reqDay + "</td>";
			strHTML += "	<td>" + quotasList[i].reqMonth + "</td>";
			strHTML += "	<td>";
			strHTML += "		<a  title=\"" + editUserQuotasToolTip
					+ "\" href=\"javascript:startEditUserQuotas('"
					+ quotasList[i].uri
					+ "')\"><img border=\"0\" src=\"images/edit.gif\"></a>";
			strHTML += "		<a   title=\"" + deleteUserToolTip
					+ "\" href=\"javascript:deleteUserQuotas('"
					+ quotasList[i].uri + "', '" + quotasList[i].serviceName
					+ "')\"><img border=\"0\" src=\"images/delete.gif\"></a>";
			strHTML += "	</td>";
			strHTML += "</tr>";
		}
		strHTML += "</table>";
	}
	strHTML += "<br>";
	strHTML += "<form>";
	strHTML += "<input type=\"button\" class=\"button_orange\" onclick=\"startEditCurrentUser()\" value=\"Done\">&nbsp;";
	strHTML += "<input type=\"button\" class=\"button_orange\" onclick=\"addUserQuotas()\" value=\"Add\">";
	strHTML += "</form>";
	c = document.getElementById('content');
	c.innerHTML = strHTML;
}
function handelUserFilterFormKeypress(e) {
	if (e.keyCode == 13) {
		showUsers();
		return false;
	}
}
function displayUserList(userList) {

	strHTML = "";
	strHTML += "<center>";
	strHTML += "<h1>";
	strHTML +=  userList.length + " users found";
	//strHTML += "Users defined in appliance:";
	strHTML += "</h1>";
	strHTML += "<hr>";
	strHTML += "<form onkeypress=\"return handelUserFilterFormKeypress(event)\">";
	strHTML += "<table class=\"tabular_table searchFormTable\" >";	
	strHTML += "	<tr class=\"tabular_table_body\">";	
	strHTML += "		<th>username</th> <td><input type=\"text\" id=\"userNameFilter\" value=\"" + userNameFilterPrevVal + "\"></td>";	
	strHTML += "		<th>email</th> <td><input type=\"text\" id=\"emailAddressFilter\" value=\"" + emailAddressFilterPrevVal + "\"></td>";	
	strHTML += "		<th>entity</th> <td><input type=\"text\" id=\"entityFilter\"value=\"" + entityFilterPrevVal + "\"></td>";	
	strHTML += "	</tr>";	
	strHTML += "	<tr class=\"tabular_table_body\">";	
	strHTML += "		<th>first name</th> <td><input type=\"text\" id=\"firstNameFilter\"value=\"" + firstNameFilterPrevVal + "\"></td>";	
	strHTML += "		<th>last name</th> <td><input type=\"text\" id=\"lastNameFilter\" value=\"" + lastNameFilterPrevVal + "\"></td>";	
	strHTML += "		<td colspan=\"2\"><input type=\"button\" class=\"button_orange\" value=\"filter\" onclick=\"showUsers()\"><input type=\"button\" class=\"button_white\" value=\"reset\" onclick=\"resetUserFilter()\"></th> ";	
	strHTML += "	</tr>";	
	strHTML += "</table>";	
	strHTML += "</form>";	
	strHTML += "<table id=\"usersList\" class=\"tabular_table scroll\">";
	strHTML += "<thead><tr class=\"tabular_table_header\">";
	strHTML += "<th>Username</th>";
	strHTML += "<th>email</th>";
	strHTML += "<th>End Date</th>";
	strHTML += "<th>Actions</th>";
	strHTML += "</tr></thead><tbody>";
	for (i = 0; i < userList.length; i++) {
		var d = new Date();
		d.setISO8601(userList[i].endDate);
		strHTML += "<tr class=\"tabular_table_body" + (i % 2) + " item\">";
		strHTML += "<td>" + userList[i].userName + "</td>";
		strHTML += "<td>" + userList[i].emailAddress + "</td>";
		strHTML += "<td>" + dateFormat(d, "fullDate") + "</td>";
		strHTML += "<td class=\"action\">";
		strHTML += "<a  title=\"" + editUserToolTip + "\" href=\"javascript:startEditUser('" + userList[i].uri+ "')\"><img border=\"0\" src=\"images/edit.gif\"></a>";
		if (userList[i].userName !== "Admin" ){
			strHTML += "<a  title=\"" + deleteUserToolTip + "\" href=\"javascript:deleteUser('" + userList[i].uri + "', '" + userList[i].userName + "')\"><img border=\"0\" src=\"images/delete.gif\"></a>";
		}
		strHTML += "</td>";
		strHTML += "</tr>";
	}
	strHTML += "</tbody></table>";
	strHTML += "</center>";

	c = document.getElementById('content');
	c.innerHTML = strHTML;
	if (userList.length>0){

		$("table.scroll").createScrollableTable({
			width: '800px',
			height: '350px',
			border: '0px'
		});
		touchScroll("usersList_body_wrap");
	}else{
		$('#usersList').hide();
	}

}

function deleteUser(userURI, userName) {

	if (confirm("Are you sure to want to remove user " + userName + "?")) {
		$.ajax({
			url : userURI,
			dataType : 'json',
			type : 'DELETE',
			// data: data,
			success : showUsers,
			error : displayErrorV2
		});
	}

}



function  resetUserFilter(){
	$('#userNameFilter').val("");
	$('#firstNameFilter').val("")
	$('#lastNameFilter').val("")
	$('#emailAddressFilter').val("");
	$('#entityFilter').val("");
	showUsers();
}
function showUsers() {
	prms="order=userName";
	
	prms=prms + "&userNameFilter=" + encodeURIComponent(getFilterValue('userNameFilter'));
	prms=prms + "&firstNameFilter=" + encodeURIComponent(getFilterValue('firstNameFilter'));
	prms=prms + "&lastNameFilter=" + encodeURIComponent(getFilterValue('lastNameFilter'));
	prms=prms + "&emailAddressFilter=" + encodeURIComponent(getFilterValue('emailAddressFilter'));
	prms=prms + "&entityFilter=" + encodeURIComponent(getFilterValue('entityFilter'));
	
	$.ajax({
		url : './users/',
		dataType : 'json',
		type : 'GET',
		data: prms,
		success : displayUserList,
		error : displayErrorV2
	});
}

// Event
$(function() {
	$('#listUser').click(resetUserFilter);
	$('#addUser').click(addUser);
})