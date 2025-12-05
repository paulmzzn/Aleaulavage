/**
 * Cookie Consent Banner JavaScript
 *
 * @package SCW_Shop
 */

(function () {
	"use strict";

	document.addEventListener("DOMContentLoaded", function () {
		const banner = document.getElementById("cookie-banner");
		const acceptBtn = document.getElementById("cookie-accept");
		const declineBtn = document.getElementById("cookie-decline");

		if (!banner) return;

		// Check if consent has already been given
		const consent = localStorage.getItem("scw_cookie_consent");

		if (!consent) {
			// Show banner after a small delay for animation
			setTimeout(function () {
				banner.style.display = "flex";
			}, 1000);
		}

		// Handle accept button
		if (acceptBtn) {
			acceptBtn.addEventListener("click", function () {
				localStorage.setItem("scw_cookie_consent", "true");
				hideBanner();
			});
		}

		// Handle decline button
		if (declineBtn) {
			declineBtn.addEventListener("click", function () {
				// In reality, you would handle declining non-essential cookies here
				localStorage.setItem("scw_cookie_consent", "false");
				hideBanner();
			});
		}

		// Hide banner with animation
		function hideBanner() {
			banner.classList.add("hidden");

			// Remove from DOM after animation completes
			setTimeout(function () {
				banner.style.display = "none";
				banner.classList.remove("hidden");
			}, 400);
		}
	});
})();
