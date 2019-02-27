$(document).ready(function(){
  var autoUpgradePanel = $("#autoupgradePhpWarn");

  $(".list-toolbar-btn", autoUpgradePanel).click(function(event) {

    event.preventDefault();
    autoUpgradePanel.fadeOut();

    $.post(
      $(this).attr("href")
    );
  });
});
