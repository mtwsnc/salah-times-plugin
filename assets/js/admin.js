jQuery(document).ready(function ($) {
  // Manual update via admin bar
  $("#wpadminbar #manual-update").on("click", function (e) {
    e.preventDefault();
    $.post(salahAjax.ajaxUrl, { action: "salah_manual_update" }, function (
      response
    ) {
      if (response.success) {
        alert("✓ " + response.data.message);
      } else {
        alert("✗ Error: " + response.data.message);
      }
    }).fail(function () {
      alert("✗ Failed to update prayer times. Please try again.");
    });
  });
});
