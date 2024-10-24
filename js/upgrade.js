if (typeof input === "undefined") {
  var input = {
    psBaseUri: "/",
    _PS_MODE_DEV_: true,
    PS_AUTOUP_BACKUP: true,
    adminUrl: "http://test.com/admin",
    adminDir: "/admin",
    token: "asdadsasdasdasd",
    txtError: [],
    firstTimeParams: {},
    ajaxUpgradeTabExists: true,
    currentIndex: "page.php",
    tab: input.tab,
    channel: "major",
    translation: {
      confirmDeleteBackup: "Are you sure you want to delete this backup?",
      delete: "Delete",
      updateInProgress:
        'An update is currently in progress... Click "OK" to abort.',
      upgradingPrestaShop: "Upgrading PrestaShop",
      upgradeComplete: "Upgrade complete",
      upgradeCompleteWithWarnings:
        "Upgrade complete, but warning notifications has been found.",
      startingRestore: "Starting restoration...",
      restoreComplete: "Restoration complete.",
      cannotDownloadFile:
        "Your server cannot download the file. Please upload it first by ftp in your admin/autoupgrade directory",
      jsonParseErrorForAction:
        "Javascript error (parseJSON) detected for action ",
      endOfProcess: "End of process",
      processCancelledCheckForRestore:
        "Operation canceled. Checking for restoration...",
      confirmRestoreBackup: "Do you want to restore SomeBackupName?",
      processCancelledWithError: "Operation canceled. An error happened.",
      missingAjaxUpgradeTab:
        "[TECHNICAL ERROR] ajax-upgradetab.php is missing. Please reinstall the module.",
      clickToRefreshAndUseNewConfiguration:
        "Click to refresh the page and use the new configuration",
      errorDetectedDuring: "Error detected during",
      downloadTimeout:
        "The request exceeded the max_time_limit. Please change your server configuration.",
      seeOrHideList: "See or hide the list",
      coreFiles: "Core file(s)",
      mailFiles: "Mail file(s)",
      translationFiles: "Translation file(s)",
      linkAndMd5CannotBeEmpty: "Link and MD5 hash cannot be empty",
      needToEnterArchiveVersionNumber:
        "You must enter the full version number of the version you want to upgrade. The full version number can be present in the zip name (ex: 1.7.8.1, 8.0.0).",
      noArchiveSelected: "No archive has been selected.",
      needToEnterDirectoryVersionNumber:
        "You need to enter the version number associated with the directory.",
      confirmSkipBackup: "Please confirm that you want to skip the backup.",
      confirmPreserveFileOptions:
        "Please confirm that you want to preserve file options.",
      lessOptions: "Less options",
      moreOptions: "More options (Expert mode)",
      filesWillBeDeleted: "These files will be deleted",
      filesWillBeReplaced: "These files will be replaced",
      noXmlSelected: "No XML file has been selected.",
      noArchiveAndXmlSelected: "No archive and no XML file have been selected.",
    },
  };
}

var firstTimeParams = input.firstTimeParams.nextParams;
firstTimeParams.firstTime = "1";

function ucFirst(str) {
  if (str.length > 0) {
    return str[0].toUpperCase() + str.substring(1);
  }
  return str;
}

function cleanInfo() {
  $("#infoStep").html("reset<br/>");
}

function updateInfoStep(msg) {
  if (msg) {
    const infoStepElement = $("#infoStep");
    infoStepElement.append(msg + '<div class="clear"></div>');
    infoStepElement.prop(
      { scrollTop: infoStepElement.prop("scrollHeight") },
      1,
    );
  }
}

function addError(error) {
  if (error && error.length) {
    $("#errorDuringUpgrade").show();
    const infoErrorElement = $("#infoError");
    if (Array.isArray(error)) {
      for (let i = 0; i < error.length; i++) {
        infoErrorElement.append(error[i] + '<div class="clear"></div>');
      }
    } else {
      infoErrorElement.append(error + '<div class="clear"></div>');
    }
    // Note: jquery 1.6 makes use of prop() instead of attr()
    infoErrorElement.prop(
      { scrollTop: infoErrorElement.prop("scrollHeight") },
      1,
    );
  }
}

function addQuickInfo(quickInfo) {
  if (quickInfo && quickInfo.length) {
    const quickInfoElement = $("#quickInfo");
    quickInfoElement.show();

    if (Array.isArray(quickInfo)) {
      for (let i = 0; i < quickInfo.length; i++) {
        quickInfoElement.append(quickInfo[i] + '<div class="clear"></div>');
      }
    } else {
      quickInfoElement.append(quickInfo + '<div class="clear"></div>');
    }
    // Note : jquery 1.6 make uses of prop() instead of attr()
    quickInfoElement.prop(
      { scrollTop: quickInfoElement.prop("scrollHeight") },
      1,
    );
  }
}

// js initialization : prepare upgrade and rollback buttons
$(document).ready(function () {
  $(".nobootstrap.no-header-toolbar")
    .removeClass("nobootstrap")
    .addClass("bootstrap");

  $(document).on("click", "a.confirmBeforeDelete", function (e) {
    if (!confirm(input.translation.confirmDeleteBackup)) {
      e.preventDefault();
    }
  });

  $("select[name=channel]").change(function (e) {
    $(this)
      .find("option")
      .each(function () {
        var $this = $(this);
        $("#for-" + $this.attr("id")).toggle($this.is(":selected"));
      });

    refreshChannelInfos();
  });

  function refreshChannelInfos() {
    var val = $("select[name=channel]").val();
    $.ajax({
      type: "POST",
      url: input.adminUrl + "/autoupgrade/ajax-upgradetab.php",
      async: true,
      data: {
        dir: input.adminDir,
        token: input.token,
        tab: input.tab,
        action: "getChannelInfo",
        ajaxMode: "1",
        params: { channel: val },
      },
      success: function (res, textStatus, jqXHR) {
        if (isJsonString(res)) {
          res = $.parseJSON(res);
        } else {
          res = { nextParams: { status: "error" } };
        }

        var answer = res.nextParams.result;
        if (typeof answer !== "undefined") {
          var $channelInfos = $("#channel-infos");
          $channelInfos.replaceWith(answer.div);
          if (answer.available) {
            $("#channel-infos .all-infos").show();
          } else {
            $channelInfos.html(answer.div);
            $("#channel-infos .all-infos").hide();
          }
        }
      },
      error: function (res, textStatus, jqXHR) {
        if (textStatus === "timeout" && action === "download") {
          updateInfoStep(input.translation.cannotDownloadFile);
        } else {
          // technical error : no translation needed
          $("#checkPrestaShopFilesVersion").html(
            '<img src="../img/admin/warning.gif" /> Error Unable to check md5 files',
          );
        }
      },
    });
  }

  // the following prevents to leave the page at the inappropriate time
  $.xhrPool = [];
  $.xhrPool.abortAll = function () {
    $.each(this, function (jqXHR) {
      if (jqXHR && jqXHR.readystate !== 4) {
        jqXHR.abort();
      }
    });
  };

  $(".upgradestep").click(function (e) {
    e.preventDefault();
    // $.scrollTo("#options")
  });

  // set timeout to 120 minutes (before aborting an ajax request)
  $.ajaxSetup({ timeout: 7200000 });

  // prepare available button here, without params ?
  prepareNextButton("#UpdateInitialization", firstTimeParams);

  /**
   * reset rollbackParams js array (used to init rollback button)
   */
  $("select[name=restoreName]").change(function () {
    var val = $(this).val();

    // show delete button if the value is not 0
    if (val != 0) {
      $("span#buttonDeleteBackup").html(
        '<br><a class="button confirmBeforeDelete" href="index.php?controller=AdminSelfUpgrade&token=' +
          input.token +
          "&amp;deletebackup&amp;name=" +
          $(this).val() +
          '"><img src="../img/admin/disabled.gif" />' +
          input.translation.delete +
          "</a>",
      );
    }

    if (val != 0) {
      $("#Restore").removeAttr("disabled");
      var rollbackParams = $.extend(true, {}, firstTimeParams);

      delete rollbackParams.backupName;
      delete rollbackParams.backupFilesFilename;
      delete rollbackParams.backupDbFilename;
      delete rollbackParams.restoreFilesFilename;
      delete rollbackParams.restoreDbFilenames;

      // init new name to backup
      rollbackParams.restoreName = val;
      prepareNextButton("#Restore", rollbackParams);
    } else {
      $("#Restore").attr("disabled", "disabled");
    }
  });

  $("div[id|=for]").hide();
  $("select[name=channel]").change();

  if (!input.ajaxUpgradeTabExists) {
    $("#checkPrestaShopFilesVersion").html(
      '<img src="../img/admin/warning.gif" />' +
        input.translation.missingAjaxUpgradeTab,
    );
  }
});

function showConfigResult(msg, type) {
  if (!type) {
    type = "conf";
  }
  var $configResult = $("#configResult");
  $configResult.html('<div class="' + type + '">' + msg + "</div>").show();

  if (type === "conf") {
    $configResult.delay(3000).fadeOut("slow", function () {
      location.reload();
    });
  }
}

// reuse previousParams, and handle xml returns to calculate next step
// (and the correct next param array)
// a case has to be defined for each requests that returns xml
function afterUpdateConfig(res) {
  var params = res.nextParams;
  var config = params.config;
  var $oldChannel = $("select[name=channel] option.current");

  if (config.channel != $oldChannel.val()) {
    var $newChannel = $(
      "select[name=channel] option[value=" + config.channel + "]",
    );
    $oldChannel.removeClass("current").html($oldChannel.html().substr(2));

    $newChannel.addClass("current").html("* " + $newChannel.html());
  }

  if (res.error == 1) {
    showConfigResult(res.next_desc, "error");
  } else {
    showConfigResult(res.next_desc);
  }

  $("#UpdateInitialization")
    .unbind()
    .replaceWith(
      '<a class="button-autoupgrade" href="' +
        input.currentIndex +
        "&token=" +
        input.token +
        '" >' +
        input.translation.clickToRefreshAndUseNewConfiguration +
        "</a>",
    );
}

function startProcess(type) {
  // hide useless divs, show activity log
  $(
    "#informationBlock,#comparisonBlock,#currentConfigurationBlock,#backupOptionsBlock,#upgradeOptionsBlock,#upgradeButtonBlock",
  ).slideUp("fast");
  $(".autoupgradeSteps a").addClass("button");
  $("#activityLogBlock").fadeIn("slow");

  $(window).bind("beforeunload", function (e) {
    if (confirm(input.translation.updateInProgress)) {
      $.xhrPool.abortAll();
      $(window).unbind("beforeunload");
      return true;
    } else {
      if (type === "upgrade") {
        e.returnValue = false;
        e.cancelBubble = true;
        if (e.stopPropagation) {
          e.stopPropagation();
        }
        if (e.preventDefault) {
          e.preventDefault();
        }
      }
    }
  });
}

function afterUpdateInitialization(res) {
  startProcess("upgrade");
  $("#UpdateInitialization")
    .unbind()
    .replaceWith(
      '<span id="upgradeNow" class="button-autoupgrade">' +
        input.translation.upgradingPrestaShop +
        " ...</span>",
    );
}

function afterBackupInitialization(res) {
  startProcess("upgrade");
  $("#UpdateInitialization")
      .unbind()
      .replaceWith(
          '<span id="upgradeNow" class="button-autoupgrade">' +
          input.translation.upgradingPrestaShop +
          " ...</span>",
      );
}

function afterUpgradeComplete(res) {
  $("#pleaseWait").hide();
  if (res.nextParams.warning_exists == "false") {
    $("#infoStep").html(`
        <p style="padding: 5px">
            <img src="${input.psBaseUri}img/admin/enabled.gif" alt="ok"> 
            ${input.translation.upgradeComplete}
        </p>
    `);
  } else {
    $("#infoStep").html(`
        <p style="padding: 5px">
            <img src="${input.psBaseUri}img/admin/warning.gif" alt="ok">
            ${input.translation.upgradeCompleteWithWarnings}
        </p>
    `);
  }

  $("#postUpdateChecklist").show();

  $(window).unbind("beforeunload");
}

function afterError(res) {
  var params = res.nextParams;
  if (params.next === "") {
    $(window).unbind("beforeunload");
  }
  $("#pleaseWait").hide();

  addQuickInfo(["unbind :) "]);
}

function afterRestore(res) {
  startProcess("rollback");
}

function afterRestoreComplete(res) {
  $("#pleaseWait").hide();
  $("#postRestoreChecklist").show();
  $(window).unbind();
}

function afterRestoreDatabase(params) {
  // $("#restoreBackupContainer").hide();
}

function afterRestoreFiles(params) {
  // $("#restoreFilesContainer").hide();
}

function afterBackupFiles(res) {
  var params = res.nextParams;
  // if (params.stepDone)
}

/**
 * afterBackupDb display the button
 */
function afterBackupDatabase(res) {
  var params = res.nextParams;

  if (res.stepDone && input.PS_AUTOUP_BACKUP === true) {
    $("#restoreBackupContainer").show();
    $("select[name=restoreName]")
      .append(
        '<option selected="selected" value="' +
          params.backupName +
          '">' +
          params.backupName +
          "</option>",
      )
      .val("")
      .change();
  }
}

function call_function(func) {
  this[func].apply(this, Array.prototype.slice.call(arguments, 1));
}

function doAjaxRequest(action, nextParams, successCallBack) {
  if (input._PS_MODE_DEV_ === true) {
    addQuickInfo(["[DEV] ajax request : " + action]);
  }
  $("#pleaseWait").show();
  $("#rollbackForm").hide();
  var req = $.ajax({
    type: "POST",
    url: input.adminUrl + "/autoupgrade/ajax-upgradetab.php",
    async: true,
    data: {
      dir: input.adminDir,
      ajaxMode: "1",
      token: input.token,
      tab: input.tab,
      action: action,
      params: nextParams,
    },
    beforeSend: (jqXHR) => $.xhrPool.push(jqXHR),
    complete: (jqXHR) => $.xhrPool.pop(),
    success: (res, textStatus, jqXHR) =>
      handleRequestSuccess(res, textStatus, jqXHR, action, successCallBack),
    error: (jqXHR, textStatus, errorThrown) =>
      handleRequestError(jqXHR, textStatus, errorThrown, action),
  });
  return req;
}

function handleRequestSuccess(res, textStatus, jqXHR, action, successCallBack) {
  $("#pleaseWait").hide();
  $("#rollbackForm").show();

  try {
    res = $.parseJSON(res);
  } catch (e) {
    addError(`${input.translation.jsonParseErrorForAction} [${action}].`);
    return;
  }

  addQuickInfo(res.nextQuickInfo);
  addError(res.nextErrors);
  updateInfoStep(res.next_desc);

  if (res.status !== "ok") {
    addError(`${input.translation.errorDetectedDuring} [${action}].`);
    return;
  }

  $("#" + action).addClass("done");
  if (res.stepDone) {
    $("#" + action).addClass("stepok");
  }
  // if a function "after[action name]" exists, it should be called now.
  // This is used for enabling restore buttons for example
  const funcName = "after" + ucFirst(action);
  if (typeof window[funcName] === "function") {
    call_function(funcName, res);
  }

  if (res.next !== "") {
    // if next is rollback, prepare nextParams with rollbackDbFilename and rollbackFilesFilename
    if (res.next === "Restore") {
      res.nextParams.restoreName = "";
    }
    doAjaxRequest(res.next, res.nextParams, successCallBack);
  } else {
    // Way To Go, end of upgrade process
    if (successCallBack) {
      successCallBack();
    }
    addQuickInfo(input.translation.endOfProcess);
  }
}

function handleRequestError(jqXHR, textStatus, errorThrown, action) {
  $("#pleaseWait").hide();
  $("#rollbackForm").show();

  if (textStatus === "timeout") {
    if (action === "download") {
      addError(input.translation.cannotDownloadFile);
    } else {
      addError(`[Server Error] Timeout: ${input.translation.downloadTimeout}`);
    }
  } else {
    try {
      const res = $.parseJSON(jqXHR.responseText);
      addQuickInfo(res.nextQuickInfo);
      addError(res.nextErrors);
      updateInfoStep(res.next_desc);
    } catch (e) {
      addError(
        `[Ajax / Server Error for action: ${action}] textStatus: ${textStatus}, errorThrown: ${errorThrown}, jqXHR: ${jqXHR.responseText}`,
      );
    }
  }
}

/**
 * prepareNextButton make the button button_selector available, and update the nextParams values
 *
 * @param button_selector $button_selector
 * @param nextParams $nextParams
 * @return void
 */
function prepareNextButton(button_selector, nextParams) {
  if (button_selector === "#Restore") {
    $("#postUpdateChecklist").hide();
  }

  $(button_selector)
    .unbind()
    .click(function (e) {
      e.preventDefault();
      $("#currentlyProcessing").show();
      var action = button_selector.substr(1);

      if (action === 'UpdateInitialization') {
        doAjaxRequest('BackupInitialization', nextParams, () => doAjaxRequest(action, nextParams));
      } else {
        doAjaxRequest(action, nextParams);
      }
    });
}

// ajax to check md5 files
function addModifiedFileList(title, fileList, css_class, container) {
  var subList = $('<ul class="changedFileList ' + css_class + '"></ul>');

  $(fileList).each(function (k, v) {
    $(subList).append("<li>" + v + "</li>");
  });

  $(container)
    .append(
      '<h3><a class="toggleSublist" href="#" >' +
        title +
        "</a> (" +
        fileList.length +
        ")</h3>",
    )
    .append(subList)
    .append("<br/>");
}

// -- Should be executed only if ajaxUpgradeTabExists

function isJsonString(str) {
  try {
    typeof str !== "undefined" && JSON.parse(str);
  } catch (e) {
    return false;
  }
  return true;
}

$(document).ready(function () {
  $.ajax({
    type: "POST",
    url: input.adminUrl + "/autoupgrade/ajax-upgradetab.php",
    async: true,
    data: {
      dir: input.adminDir,
      token: input.token,
      tab: input.tab,
      action: "CheckFilesVersion",
      ajaxMode: "1",
      params: {},
    },
    success: function (res, textStatus, jqXHR) {
      if (isJsonString(res)) {
        res = $.parseJSON(res);
      } else {
        res = { nextParams: { status: "error" } };
      }
      var answer = res.nextParams;
      var $checkPrestaShopFilesVersion = $("#checkPrestaShopFilesVersion");

      $checkPrestaShopFilesVersion.html("<span> " + answer.msg + " </span> ");
      if (answer.status === "error" || typeof answer.result === "undefined") {
        $checkPrestaShopFilesVersion.prepend(
          '<img src="../img/admin/warning.gif" /> ',
        );
      } else {
        $checkPrestaShopFilesVersion
          .prepend('<img src="../img/admin/warning.gif" /> ')
          .append(
            '<a id="toggleChangedList" class="button" href="">' +
              input.translation.seeOrHideList +
              "</a><br/>",
          )
          .append('<div id="changedList" style="display:none "><br/>');

        if (answer.result.core.length) {
          addModifiedFileList(
            input.translation.coreFiles,
            answer.result.core,
            "changedImportant",
            "#changedList",
          );
        }
        if (answer.result.mail.length) {
          addModifiedFileList(
            input.translation.mailFiles,
            answer.result.mail,
            "changedNotice",
            "#changedList",
          );
        }
        if (answer.result.translation.length) {
          addModifiedFileList(
            input.translation.translationFiles,
            answer.result.translation,
            "changedNotice",
            "#changedList",
          );
        }

        $("#toggleChangedList").bind("click", function (e) {
          e.preventDefault();
          $("#changedList").toggle();
        });

        $("body")
          .on()
          .on("click", ".toggleSublist", function (e) {
            e.preventDefault();
            $(this).parent().next().toggle();
          });
      }
    },
    error: function (res, textStatus, jqXHR) {
      if (textStatus === "timeout" && action === "download") {
        updateInfoStep(input.translation.cannotDownloadFile);
      } else {
        // technical error : no translation needed
        $("#checkPrestaShopFilesVersion").html(
          '<img src="../img/admin/warning.gif" /> Error: Unable to check md5 files',
        );
      }
    },
  });

  $.ajax({
    type: "POST",
    url: input.adminUrl + "/autoupgrade/ajax-upgradetab.php",
    async: true,
    data: {
      dir: input.adminDir,
      token: input.token,
      tab: input.tab,
      action: "CompareReleases",
      ajaxMode: "1",
      params: {},
    },
    success: function (res, textStatus, jqXHR) {
      if (isJsonString(res)) {
        res = $.parseJSON(res);
      } else {
        res = { nextParams: { status: "error" } };
      }
      var answer = res.nextParams;
      var $checkPrestaShopModifiedFiles = $("#checkPrestaShopModifiedFiles");

      $checkPrestaShopModifiedFiles.html("<span> " + answer.msg + " </span> ");
      if (answer.status === "error" || typeof answer.result === "undefined") {
        $checkPrestaShopModifiedFiles.prepend(
          '<img src="../img/admin/warning.gif" /> ',
        );
      } else {
        $checkPrestaShopModifiedFiles
          .prepend('<img src="../img/admin/warning.gif" /> ')
          .append(
            '<a id="toggleDiffList" class="button" href="">' +
              input.translation.seeOrHideList +
              "</a><br/>",
          )
          .append('<div id="diffList" style="display:none "><br/>');

        if (answer.result.deleted.length) {
          addModifiedFileList(
            input.translation.filesWillBeDeleted,
            answer.result.deleted,
            "diffImportant",
            "#diffList",
          );
        }
        if (answer.result.modified.length) {
          addModifiedFileList(
            input.translation.filesWillBeReplaced,
            answer.result.modified,
            "diffImportant",
            "#diffList",
          );
        }

        $("#toggleDiffList").bind("click", function (e) {
          e.preventDefault();
          $("#diffList").toggle();
        });

        $("body")
          .on()
          .on("click", ".toggleSublist", function (e) {
            e.preventDefault();
            // this=a, parent=h3, next=ul
            $(this).parent().next().toggle();
          });
      }
    },
    error: function (res, textStatus, jqXHR) {
      if (textStatus === "timeout" && action === "download") {
        updateInfoStep(input.translation.cannotDownloadFile);
      } else {
        // technical error : no translation needed
        $("#checkPrestaShopFilesVersion").html(
          '<img src="../img/admin/warning.gif" /> Error: Unable to check md5 files',
        );
      }
    },
  });
});

// -- END

// advanced/normal mode
function switch_to_advanced() {
  $("input[name=btn_adv]").val(input.translation.lessOptions);
  $("#advanced").show();
}

function switch_to_normal() {
  $("input[name=btn_adv]").val(input.translation.moreOptions);
  $("#advanced").hide();
}

$("input[name=btn_adv]").click(function (e) {
  if ($("#advanced:visible").length) {
    switch_to_normal();
  } else {
    switch_to_advanced();
  }
});

$(document).ready(function () {
  $("input[name|=submitConf], input[name=submitConf-channel]").bind(
    "click",
    function (e) {
      var params = {};
      var $newChannel = $("select[name=channel] option:selected").val();
      var $oldChannel = $("select[name=channel] option.current").val();

      $oldChannel = "";

      if ($oldChannel != $newChannel) {
        var validChannels = ["online"];
        if (validChannels.indexOf($newChannel) !== -1) {
          params.channel = $newChannel;
        }

        if ($newChannel === "local") {
          var archive_zip = $("select[name=archive_zip]").val();
          var archive_xml = $("select[name=archive_xml]").val();
          if (!archive_zip && !archive_xml) {
            showConfigResult(
              input.translation.noArchiveAndXmlSelected,
              "error",
            );
            return false;
          } else if (!archive_zip) {
            showConfigResult(input.translation.noArchiveSelected, "error");
            return false;
          } else if (!archive_xml) {
            showConfigResult(input.translation.noXmlSelected, "error");
            return false;
          }
          params.channel = "local";
          params.archive_zip = archive_zip;
          params.archive_xml = archive_xml;
        }
      }
      // note: skipBackup is currently not used
      if ($(this).attr("name") == "submitConf-skipBackup") {
        var skipBackup = $("input[name=submitConf-skipBackup]:checked").length;
        if (skipBackup == 0 || confirm(input.translation.confirmSkipBackup)) {
          params.skip_backup = $(
            "input[name=submitConf-skipBackup]:checked",
          ).length;
        } else {
          $("input[name=submitConf-skipBackup]:checked").removeAttr("checked");
          return false;
        }
      }

      // note: preserveFiles is currently not used
      if ($(this).attr("name") == "submitConf-preserveFiles") {
        var preserveFiles = $(
          "input[name=submitConf-preserveFiles]:checked",
        ).length;
        if (confirm(input.translation.confirmPreserveFileOptions)) {
          params.preserve_files = $(
            "input[name=submitConf-preserveFiles]:checked",
          ).length;
        } else {
          $("input[name=submitConf-skipBackup]:checked").removeAttr("checked");
          return false;
        }
      }
      var res = doAjaxRequest("UpdateConfig", params);
    },
  );
});
