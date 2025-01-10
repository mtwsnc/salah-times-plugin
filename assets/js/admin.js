jQuery(document).ready(function ($) {
  // Manual update via admin bar
  $("#wpadminbar #manual-update").on("click", function (e) {
    e.preventDefault();
    $.post(
      salahAjax.ajaxUrl,
      { action: "salah_manual_update" },
      function (response) {
        let result = JSON.parse(response);
        alert(result.success ? result.message : result.error);
      }
    );
  });
});
