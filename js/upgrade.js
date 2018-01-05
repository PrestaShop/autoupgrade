/* 
 * 2007-2017 PrestaShop
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 * 
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2017 PrestaShop SA
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */


function ucFirst(str) {
    if (str.length > 0) {
        return str[0].toUpperCase() + str.substring(1);
    }
    return str;
}

function cleanInfo(){
	$("#infoStep").html("reset<br/>");
}

function updateInfoStep(msg){
	if (msg)
	{
		$("#infoStep").append(msg+"<div class=\"clear\"></div>");
		$("#infoStep").prop({ scrollTop: $("#infoStep").prop("scrollHeight")},1);
	}
}

function addError(arrError){
	if (typeof(arrError) != "undefined" && arrError.length)
	{
		$("#errorDuringUpgrade").show();
		for(i=0;i<arrError.length;i++)
			$("#infoError").append(arrError[i]+"<div class=\"clear\"></div>");
		// Note : jquery 1.6 make uses of prop() instead of attr()
		$("#infoError").prop({ scrollTop: $("#infoError").prop("scrollHeight")},1);
	}
}

function addQuickInfo(arrQuickInfo){
	if (arrQuickInfo)
	{
		$("#quickInfo").show();
		for(i=0;i<arrQuickInfo.length;i++)
			$("#quickInfo").append(arrQuickInfo[i]+"<div class=\"clear\"></div>");
		// Note : jquery 1.6 make uses of prop() instead of attr()
		$("#quickInfo").prop({ scrollTop: $("#quickInfo").prop("scrollHeight")},1);
	}
}

// js initialization : prepare upgrade and rollback buttons
$(document).ready(function(){

    $(".nobootstrap.no-header-toolbar").removeClass("nobootstrap").addClass("bootstrap");

    $(document).on("click", "a.confirmBeforeDelete", function(e){
        if (!confirm(translated[3]))
            e.preventDefault();
    });

	$("select[name=channel]").change(function(e){
		$("select[name=channel]").find("option").each(function()
		{
			if ($(this).is(":selected"))
				$("#for-"+$(this).attr("id")).show();
			else
				$("#for-"+$(this).attr("id")).hide();
	});

		refreshChannelInfos();
	});

	function refreshChannelInfos()
	{
		val = $("select[name=channel]").find("option:selected").val();
		$.ajax({
			type:"POST",
			url : "'. __PS_BASE_URI__ . $admin_dir.'/autoupgrade/ajax-upgradetab.php",
			async: true,
			data : {
				dir:"'.$admin_dir.'",
				token : "'.$this->token.'",
				tab : "AdminSelfUpgrade",
				action : "getChannelInfo",
				ajaxMode : "1",
				params : { channel : val}
			},
			success : function(res,textStatus,jqXHR)
			{
				if (isJsonString(res))
					res = $.parseJSON(res);
				else
					res = {nextParams:{status:"error"}};

				answer = res.nextParams.result;
				if (typeof(answer) != "undefined")
				$("#channel-infos").replaceWith(answer.div);
				if (typeof(answer) != "undefined" && answer.available)
				{
					$("#channel-infos .all-infos").show();
				}
				else if (typeof(answer) != "undefined")
				{
					$("#channel-infos").html(answer.div);
					$("#channel-infos .all-infos").hide();
				}
			},
			error: function(res, textStatus, jqXHR)
			{
				if (textStatus == "timeout" && action == "download")
				{
					updateInfoStep(translated[4]);
				}
				else
				{
					// technical error : no translation needed
					$("#checkPrestaShopFilesVersion").html("<img src=\"../img/admin/warning.gif\" /> Error Unable to check md5 files");
				}
			}
		})
	}

	$(document).ready(function(){
		$("div[id|=for]").hide();
		$("select[name=channel]").change();
	});

	// the following prevents to leave the page at the innappropriate time
	$.xhrPool = [];
	$.xhrPool.abortAll = function()
	{
		$.each(this, function(jqXHR)
		{
			if (jqXHR && (jqXHR.readystate != 4))
			{
				jqXHR.abort();
			}
		});
	}
	$(".upgradestep").click(function(e)
	{
		e.preventDefault();
		// $.scrollTo("#options")
	});

	// set timeout to 120 minutes (before aborting an ajax request)
	$.ajaxSetup({timeout:7200000});

	// prepare available button here, without params ?
	prepareNextButton("#upgradeNow",firstTimeParams);

	/**
	 * reset rollbackParams js array (used to init rollback button)
	 */
	$("select[name=restoreName]").change(function(){
		// show delete button if the value is not 0
		if($(this).val() != 0)
		{
			$("span#buttonDeleteBackup").html("<br><a class=\"button confirmBeforeDelete\" href=\"index.php?tab=AdminSelfUpgrade&token="+
            token
            +"&amp;deletebackup&amp;name="+$(this).val()+"\">\
            <img src=\"../img/admin/disabled.gif\" />"+translated[0]+"</a>");
		}

		if ($("select[name=restoreName]").val() != 0)
		{
			$("#rollback").removeAttr("disabled");
			rollbackParams = jQuery.extend(true, {}, firstTimeParams);

			delete rollbackParams.backupName;
			delete rollbackParams.backupFilesFilename;
			delete rollbackParams.backupDbFilename;
			delete rollbackParams.restoreFilesFilename;
			delete rollbackParams.restoreDbFilenames;

			// init new name to backup
			rollbackParams.restoreName = $("select[name=restoreName]").val();
			prepareNextButton("#rollback", rollbackParams);
			// Note : theses buttons have been removed.
			// they will be available in a future release (when DEV_MODE and MANUAL_MODE enabled)
			// prepareNextButton("#restoreDb", rollbackParams);
			// prepareNextButton("#restoreFiles", rollbackParams);
		}
		else
			$("#rollback").attr("disabled", "disabled");
	});

});

function showConfigResult(msg, type){
	if (type == null)
		type = "conf";
	$("#configResult").html("<div class=\""+type+"\">"+msg+"</div>").show();
	if (type == "conf")
	{
		$("#configResult").delay(3000).fadeOut("slow", function() {
			location.reload();
		});
	}
}

// reuse previousParams, and handle xml returns to calculate next step
// (and the correct next param array)
// a case has to be defined for each requests that returns xml


function afterUpdateConfig(res)
{
	params = res.nextParams
	config = params.config
	oldChannel = $("select[name=channel] option.current");
	if (config.channel != oldChannel.val())
	{
		newChannel = $("select[name=channel] option[value="+config.channel+"]");
		oldChannel.removeClass("current");
		oldChannel.html(oldChannel.html().substr(2));
		newChannel.addClass("current");
		newChannel.html("* "+newChannel.html());
	}
	if (res.error == 1)
		showConfigResult(res.next_desc, "error");
	else
		showConfigResult(res.next_desc);
	$("#upgradeNow").unbind();
	$("#upgradeNow").replaceWith("<a class=\"button-autoupgrade\" href=\""+currentIndex+"&token="+token+"\" >"+translated[5]+"</a>");
}
function startProcess(type){

	// hide useless divs, show activity log
	$("#informationBlock,#comparisonBlock,#currentConfigurationBlock,#backupOptionsBlock,#upgradeOptionsBlock,#upgradeButtonBlock").slideUp("fast");
	$(".autoupgradeSteps a").addClass("button");
	$("#activityLogBlock").fadeIn("slow");

	$(window).bind("beforeunload", function(e)
	{
		if (confirm(translated[6]))
		{
			$.xhrPool.abortAll();
			$(window).unbind("beforeunload");
			return true;
		}
		else
		{
			if (type == "upgrade")
			{
				e.returnValue = false;
				e.cancelBubble = true;
				if (e.stopPropagation)
				{
					e.stopPropagation();
				}
				if (e.preventDefault)
				{
					e.preventDefault();
				}
			}
		}
	});
}

function afterUpgradeNow(res)
{
	startProcess("upgrade");
	$("#upgradeNow").unbind();
	$("#upgradeNow").replaceWith("<span id=\"upgradeNow\" class=\"button-autoupgrade\">"+translated[7]+"</span>");
}

function afterUpgradeComplete(res)
{
	params = res.nextParams
	$("#pleaseWait").hide();
	if (params.warning_exists == "false")
	{
		$("#upgradeResultCheck")
			.html("<p>"+translated[8]+"</p>")
			.show();
		$("#infoStep").html("<p class=\"alert alert-success\">"+translated[8]+"</p>");
	}
	else
	{
		params = res.nextParams
		$("#pleaseWait").hide();
		$("#upgradeResultCheck")
			.html("<p>"+translated[10]+"</p>")
			.show("slow");
		$("#infoStep").html("<p class=\"alert alert-warning\">"+translated[10]+"</p>");
	}

	todo_list = [
		translated[11],
		translated[12],
		translated[13],
		translated[14],
		translated[15],
	];

	todo_ul = "<ul>";
	$("#upgradeResultToDoList")
		.html("<strong>"+translated[16]+"</strong>")
	for(var i in todo_list)
	{
		todo_ul += "<li>"+todo_list[i]+"</li>";
	}
	todo_ul += "</ul>";
	$("#upgradeResultToDoList").append(todo_ul)
	$("#upgradeResultToDoList").show();

	$(window).unbind("beforeunload");
}

function afterError(res)
{
	params = res.nextParams;
	if (params.next == "")
		$(window).unbind("beforeunload");
	$("#pleaseWait").hide();

	addQuickInfo(["unbind :) "]);
}

function afterRollback(res)
{
	startProcess("rollback");
}

function afterRollbackComplete(res)
{
	params = res.nextParams
	$("#pleaseWait").hide();
	$("#upgradeResultCheck")
		.html("<p>"+translated[17]+"</p>")
		.show("slow");
	updateInfoStep("<p class=\"alert alert-success\">"+translated[17]+"</p>");
	$(window).unbind();
}


function afterRestoreDb(params)
{
	// $("#restoreBackupContainer").hide();
}

function afterRestoreFiles(params)
{
	// $("#restoreFilesContainer").hide();
}

function afterBackupFiles(res)
{
	params = res.nextParams;
	// if (params.stepDone)
}

/**
 * afterBackupDb display the button
 *
 */
function afterBackupDb(res)
{
	params = res.nextParams;
	if (res.stepDone && typeof(PS_AUTOUP_BACKUP) != "undefined" && PS_AUTOUP_BACKUP == true)
	{
		$("#restoreBackupContainer").show();
		$("select[name=restoreName]").children("options").removeAttr("selected");
		$("select[name=restoreName]")
			.append("<option selected=\"selected\" value=\""+params.backupName+"\">"+params.backupName+"</option>")
		$("select[name=restoreName]").change();
	}
}


function call_function(func){
	this[func].apply(this, Array.prototype.slice.call(arguments, 1));
}

function doAjaxRequest(action, nextParams){
	if (typeof(_PS_MODE_DEV_) != "undefined" && _PS_MODE_DEV_ == true)
		addQuickInfo(["[DEV] ajax request : " + action]);
	$("#pleaseWait").show();
	req = $.ajax({
		type:"POST",
		url : adminUrl+"/autoupgrade/ajax-upgradetab.php",
		async: true,
		data : {
			dir: adminDir,
			ajaxMode : "1",
			token : token,
			tab : "AdminSelfUpgrade",
			action : action,
			params : nextParams
		},
		beforeSend: function(jqXHR)
		{
			$.xhrPool.push(jqXHR);
		},
		complete: function(jqXHR)
		{
			// just remove the item to the "abort list"
			$.xhrPool.pop();
			// $(window).unbind("beforeunload");
		},
		success : function(res, textStatus, jqXHR)
		{
			$("#pleaseWait").hide();
			try{
				res = $.parseJSON(res);
			}
			catch(e){
				res = {status : "error", nextParams:nextParams};
				alert(translated[1]+"\""+action+"\""+translated[2]);
			}

			addQuickInfo(res.nextQuickInfo);
			addError(res.nextErrors);
			updateInfoStep(res.next_desc);
			currentParams = res.nextParams;
			if (res.status == "ok")
			{
				$("#"+action).addClass("done");
				if (res.stepDone)
					$("#"+action).addClass("stepok");
				// if a function "after[action name]" exists, it should be called now.
				// This is used for enabling restore buttons for example
				funcName = "after" + ucFirst(action);
				if (typeof funcName == "string" && eval("typeof " + funcName) == "function")
					call_function(funcName, res);

				handleSuccess(res, action);
			} else {
				// display progression
				$("#"+action).addClass("done");
				$("#"+action).addClass("steperror");
				if (action != "rollback"
					&& action != "rollbackComplete"
					&& action != "restoreFiles"
					&& action != "restoreDb"
					&& action != "rollback"
					&& action != "noRollbackFound"
				)
					handleError(res, action);
				else
					alert(translated[18]+ " [" + action + "].");
			}
		},
		error: function(jqXHR, textStatus, errorThrown)
		{
			$("#pleaseWait").hide();
			if (textStatus == "timeout")
			{
				if (action == "download")
					updateInfoStep(translated[4]);
				else
					updateInfoStep("[Server Error] Timeout: "+ translated[19]);
			}
			else
				updateInfoStep("[Ajax / Server Error for action " + action + "] textStatus: \"" + textStatus + " \" errorThrown:\"" + errorThrown + " \" jqXHR: \" " + jqXHR.responseText + "\"");
		}
	});
	return req;
};

/**
 * prepareNextButton make the button button_selector available, and update the nextParams values
 *
 * @param button_selector $button_selector
 * @param nextParams $nextParams
 * @return void
 */
function prepareNextButton(button_selector, nextParams)
{
	$(button_selector).unbind();
	$(button_selector).click(function(e){
		e.preventDefault();
		$("#currentlyProcessing").show();
        
	action = button_selector.substr(1);
	res = doAjaxRequest(action, nextParams);
	});
}

/**
 * handleSuccess
 * res = {error:, next:, next_desc:, nextParams:, nextQuickInfo:,status:"ok"}
 * @param res $res
 * @return void
 */
function handleSuccess(res, action)
{
	if (res.next != "")
	{

		$("#"+res.next).addClass("nextStep");
		if (manualMode && (action != "rollback"
						&& action != "rollbackComplete"
						&& action != "restoreFiles"
						&& action != "restoreDb"
						&& action != "rollback"
						&& action != "noRollbackFound"))
		{
			prepareNextButton("#"+res.next,res.nextParams);
			alert(translated[20].replace('%s', res.next));
		}
		else
		{
			// if next is rollback, prepare nextParams with rollbackDbFilename and rollbackFilesFilename
			if ( res.next == "rollback")
			{
				res.nextParams.restoreName = ""
			}
			doAjaxRequest(res.next, res.nextParams);
			// 2) remove all step link (or show them only in dev mode)
			// 3) when steps link displayed, they should change color when passed if they are visible
		}
	}
	else
	{
		// Way To Go, end of upgrade process
		addQuickInfo([translated[21]]);
	}
}

// res = {nextParams, next_desc}
function handleError(res, action)
{
	// display error message in the main process thing
	// In case the rollback button has been deactivated, just re-enable it
	$("#rollback").removeAttr("disabled");
	// auto rollback only if current action is upgradeFiles or upgradeDb
	if (action == "upgradeFiles" || action == "upgradeDb" || action == "upgradeModules" )
	{
		$(".button-autoupgrade").html(translated[22]);
		res.nextParams.restoreName = res.nextParams.backupName;
		if (confirm(translated[23].replace('%backupname%', res.nextParams.backupName)))
			doAjaxRequest("rollback",res.nextParams);
	}
	else
	{
		$(".button-autoupgrade").html(translated[24]);
		$(window).unbind();
	}
}

function addModifiedFileList(title, fileList, css_class, container)
{
	subList = $("<ul class=\"changedFileList "+css_class+"\"></ul>");

	$(fileList).each(function(k,v){
		$(subList).append("<li>"+v+"</li>");
	});
	$(container).append("<h3><a class=\"toggleSublist\" href=\"#\" >"+title+"</a> (" + fileList.length + ")</h3>");
	$(container).append(subList);
	$(container).append("<br/>");

}

function isJsonString(str) {
    try {
            typeof(str) != "undefined" && JSON.parse(str);
    } catch (e) {
            return false;
    }
    return true;
}

$(document).ready(function(){
	$.ajax({
			type:"POST",
			url : adminUrl+"/autoupgrade/ajax-upgradetab.php",
			async: true,
			data : {
				dir: adminDir,
				token : token,
				tab : "AdminSelfUpgrade",
				action : "checkFilesVersion",
				ajaxMode : "1",
				params : {}
			},
			success : function(res,textStatus,jqXHR)
			{
				if (isJsonString(res))
					res = $.parseJSON(res);
				else
				{
					res = {nextParams:{status:"error"}};
				}
					answer = res.nextParams;
					$("#checkPrestaShopFilesVersion").html("<span> "+answer.msg+" </span> ");
					if ((answer.status == "error") || (typeof(answer.result) == "undefined"))
						$("#checkPrestaShopFilesVersion").prepend("<img src=\"../img/admin/warning.gif\" /> ");
					else
					{
						$("#checkPrestaShopFilesVersion").prepend("<img src=\"../img/admin/warning.gif\" /> ");
						$("#checkPrestaShopFilesVersion").append("<a id=\"toggleChangedList\" class=\"button\" href=\"\">"+translated[25]+"</a><br/>");
						$("#checkPrestaShopFilesVersion").append("<div id=\"changedList\" style=\"display:none \"><br/>");
						if(answer.result.core.length)
							addModifiedFileList(translated[26], answer.result.core, "changedImportant", "#changedList");
						if(answer.result.mail.length)
							addModifiedFileList(translated[27], answer.result.mail, "changedNotice", "#changedList");
						if(answer.result.translation.length)
							addModifiedFileList(translated[28], answer.result.translation, "changedNotice", "#changedList");

						$("#toggleChangedList").bind("click",function(e){e.preventDefault();$("#changedList").toggle();});
						$(".toggleSublist").die().live("click",function(e){e.preventDefault();$(this).parent().next().toggle();});
					}
			}
			,
			error: function(res, textStatus, jqXHR)
			{
				if (textStatus == "timeout" && action == "download")
				{
					updateInfoStep(translated[29]);
				}
				else
				{
					// technical error : no translation needed
					$("#checkPrestaShopFilesVersion").html("<img src=\"../img/admin/warning.gif\" /> Error: Unable to check md5 files");
				}
			}
		})
	$.ajax({
			type:"POST",
			url : adminUrl+"/autoupgrade/ajax-upgradetab.php",
			async: true,
			data : {
				dir: adminDir,
				token : token,
				tab : "AdminSelfUpgrade",
				action : "compareReleases",
				ajaxMode : "1",
				params : {}
			},
			success : function(res,textStatus,jqXHR)
			{
				if (isJsonString(res))
					res = $.parseJSON(res);
				else
				{
					res = {nextParams:{status:"error"}};
				}
				answer = res.nextParams;
				$("#checkPrestaShopModifiedFiles").html("<span> "+answer.msg+" </span> ");
				if ((answer.status == "error") || (typeof(answer.result) == "undefined"))
					$("#checkPrestaShopModifiedFiles").prepend("<img src=\"../img/admin/warning.gif\" /> ");
				else
				{
					$("#checkPrestaShopModifiedFiles").prepend("<img src=\"../img/admin/warning.gif\" /> ");
					$("#checkPrestaShopModifiedFiles").append("<a id=\"toggleDiffList\" class=\"button\" href=\"\">"+translated[30]+"</a><br/>");
					$("#checkPrestaShopModifiedFiles").append("<div id=\"diffList\" style=\"display:none \"><br/>");
						if(answer.result.deleted.length)
							addModifiedFileList(translated[31], answer.result.deleted, "diffImportant", "#diffList");
						if(answer.result.modified.length)
							addModifiedFileList(translated[32], answer.result.modified, "diffImportant", "#diffList");

					$("#toggleDiffList").bind("click",function(e){e.preventDefault();$("#diffList").toggle();});
					$(".toggleSublist").die().live("click",function(e){
						e.preventDefault();
						// this=a, parent=h3, next=ul
						$(this).parent().next().toggle();
					});
				}
			},
			error: function(res, textStatus, jqXHR)
			{
				if (textStatus == "timeout" && action == "download")
				{
					updateInfoStep(translated[33]);
				}
				else
				{
					// technical error : no translation needed
					$("#checkPrestaShopFilesVersion").html("<img src=\"../img/admin/warning.gif\" /> Error: Unable to check md5 files");
				}
			}
		})
	});
    
        $("input[name=btn_adv]").click(function(e)
		{
			if ($("#advanced:visible").length)
				switch_to_normal();
			else
				switch_to_advanced();
		});

		function switch_to_advanced(){
			$("input[name=btn_adv]").val(translated[34]);
			$("#advanced").show();
		}

		function switch_to_normal(){
			$("input[name=btn_adv]").val(translated[35]);
			$("#advanced").hide();
		}

		$(document).ready(function(){
			defaultMode();
		});


$(document).ready(function()
{
	$("input[name|=submitConf]").bind("click", function(e){
		params = {};
		newChannel = $("select[name=channel] option:selected").val();
		oldChannel = $("select[name=channel] option.current").val();
		oldChannel = "";
		if (oldChannel != newChannel)
		{
			if( newChannel == "major"
				|| newChannel == "minor"
				|| newChannel == "rc"
				|| newChannel == "beta"
				|| newChannel == "alpha" )
				params.channel = newChannel;

			if(newChannel == "private")
			{
				if (($("input[name=private_release_link]").val() == "") || ($("input[name=private_release_md5]").val() == ""))
				{
					showConfigResult(translated[36], "error");
					return false;
				}
				params.channel = "private";
				params.private_release_link = $("input[name=private_release_link]").val();
				params.private_release_md5 = $("input[name=private_release_md5]").val();
				if ($("input[name=private_allow_major]").is(":checked"))
					params.private_allow_major = 1;
				else
					params.private_allow_major = 0;
			}
			if(newChannel == "archive")
			{
				archive_prestashop = $("select[name=archive_prestashop] option:selected").val();
				archive_num = $("input[name=archive_num]").val();
				if (archive_num == "")
				{
					showConfigResult(translated[37], "error");
					return false;
				}
				if (archive_prestashop == "")
				{
					showConfigResult(translated[38], "error");
					return false;
				}
				params.channel = "archive";
				params.archive_prestashop = archive_prestashop;
				params.archive_num = archive_num;
			}
			if(newChannel == "directory")
			{
				params.channel = "directory";
				params.directory_prestashop = $("select[name=directory_prestashop] option:selected").val();
				directory_num = $("input[name=directory_num]").val();
				if (directory_num == "" || directory_num.indexOf(".") == -1)
				{
					showConfigResult(translated[39], "error");
					return false;
				}
				params.directory_num = $("input[name=directory_num]").val();
			}
		}
		// note: skipBackup is currently not used
		if ($(this).attr("name") == "submitConf-skipBackup")
		{
			skipBackup = $("input[name=submitConf-skipBackup]:checked").length;
			if (skipBackup == 0 || confirm(translated[40]))
				params.skip_backup = $("input[name=submitConf-skipBackup]:checked").length;
			else
			{
				$("input[name=submitConf-skipBackup]:checked").removeAttr("checked");
				return false;
			}
		}

		// note: preserveFiles is currently not used
		if ($(this).attr("name") == "submitConf-preserveFiles")
		{
			preserveFiles = $("input[name=submitConf-preserveFiles]:checked").length;
			if (confirm(translated[41]))
				params.preserve_files = $("input[name=submitConf-preserveFiles]:checked").length;
			else
			{
				$("input[name=submitConf-skipBackup]:checked").removeAttr("checked");
				return false;
			}
		}
		res = doAjaxRequest("updateConfig", params);
	});
});