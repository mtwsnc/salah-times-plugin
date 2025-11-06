/**
 * Salah Times Countdown Timer
 * Updates countdown every second
 */

(function () {
  "use strict";

  function updateCountdown() {
    const countdownElements = document.querySelectorAll(".countdown");

    countdownElements.forEach(function (element) {
      const targetTimestamp = parseInt(element.getAttribute("data-timestamp"));

      if (!targetTimestamp) {
        return;
      }

      const now = Math.floor(Date.now() / 1000);
      const diff = targetTimestamp - now;

      if (diff <= 0) {
        // Time has passed, reload to get new prayer times
        window.location.reload();
        return;
      }

      const hours = Math.floor(diff / 3600);
      const minutes = Math.floor((diff % 3600) / 60);
      const seconds = diff % 60;

      element.textContent = hours + "h " + minutes + "m " + seconds + "s";
    });
  }

  function highlightCurrentPrayer() {
    // Re-evaluate current prayer every minute
    const now = new Date();
    const currentTime = now.getHours() * 3600 + now.getMinutes() * 60;

    const rows = document.querySelectorAll(".salah-times-table tbody tr");

    rows.forEach(function (row) {
      const timeCell = row.querySelector(".prayer-time");
      if (!timeCell) return;

      const timeText = timeCell.textContent;
      const timeParts = timeText.match(/(\d+):(\d+)\s*(AM|PM)/i);

      if (timeParts) {
        let hours = parseInt(timeParts[1]);
        const minutes = parseInt(timeParts[2]);
        const meridiem = timeParts[3].toUpperCase();

        // Convert to 24-hour format
        if (meridiem === "PM" && hours !== 12) {
          hours += 12;
        } else if (meridiem === "AM" && hours === 12) {
          hours = 0;
        }

        const prayerTime = hours * 3600 + minutes * 60;
        // This is simplified - full logic would need server-side support
      }
    });
  }

  // Update countdown every second
  if (document.querySelector(".countdown")) {
    updateCountdown();
    setInterval(updateCountdown, 1000);
  }

  // Re-evaluate current prayer every minute
  setInterval(highlightCurrentPrayer, 60000);

  // Refresh prayer times at midnight
  function scheduleAutoRefresh() {
    const now = new Date();
    const tomorrow = new Date(
      now.getFullYear(),
      now.getMonth(),
      now.getDate() + 1
    );
    const msUntilMidnight = tomorrow - now;

    setTimeout(function () {
      window.location.reload();
    }, msUntilMidnight);
  }

  scheduleAutoRefresh();

  // Handle page visibility changes (when user returns to tab)
  document.addEventListener("visibilitychange", function () {
    if (!document.hidden) {
      updateCountdown();
    }
  });
})();
