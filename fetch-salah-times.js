function fetchSalahTimes() {
  fetch(ajaxurl + "?action=fetch_salah_times", {
    method: "POST",
    credentials: "same-origin",
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        alert("Salah times fetched successfully!");
        location.reload();
      } else {
        alert("Error: " + data.data);
      }
    })
    .catch((error) => alert("Error: " + error));
}
