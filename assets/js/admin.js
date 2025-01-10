jQuery(document).ready(function ($) {
  $("#manual-update").on("click", function () {
    $.post(
      salahAjax.ajaxUrl,
      { action: "salah_manual_update" },
      function (response) {
        let result = JSON.parse(response);
        $("#update-result").text(
          result.success ? result.message : result.error
        );
      }
    );
  });
});
