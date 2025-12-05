/**
 * Product Detail JavaScript
 * Handles tabs, quantity selector, gallery, and price editor
 *
 * @package SCW_Shop
 */

(function () {
	"use strict";

	document.addEventListener("DOMContentLoaded", function () {
		// ═══════════════════════════════════════════════════════════
		// TABS
		// ═══════════════════════════════════════════════════════════
		const tabButtons = document.querySelectorAll(".tab-btn");
		const tabPanels = document.querySelectorAll(".tab-panel");

		tabButtons.forEach((button) => {
			button.addEventListener("click", function () {
				const tabName = this.getAttribute("data-tab");

				// Remove active class from all buttons and panels
				tabButtons.forEach((btn) => btn.classList.remove("active"));
				tabPanels.forEach((panel) => panel.classList.remove("active"));

				// Add active class to clicked button and corresponding panel
				this.classList.add("active");
				const targetPanel = document.querySelector(
					`.tab-panel[data-panel="${tabName}"]`
				);
				if (targetPanel) {
					targetPanel.classList.add("active");
				}
			});
		});

		// ═══════════════════════════════════════════════════════════
		// GALLERY - Thumbnail click to change main image
		// ═══════════════════════════════════════════════════════════
		const thumbnails = document.querySelectorAll(".thumbnails .thumb");
		const mainImage = document.getElementById("main-image-src");

		thumbnails.forEach((thumb) => {
			thumb.addEventListener("click", function () {
				const fullUrl = this.getAttribute("data-full");
				if (fullUrl && mainImage) {
					mainImage.src = fullUrl;
					
					// Update active state
					thumbnails.forEach((t) => t.classList.remove("active"));
					this.classList.add("active");
				}
			});
		});

		// ═══════════════════════════════════════════════════════════
		// QUANTITY SELECTOR
		// ═══════════════════════════════════════════════════════════
		const qtyMinusButtons = document.querySelectorAll(".qty-btn.minus");
		const qtyPlusButtons = document.querySelectorAll(".qty-btn.plus");

		qtyMinusButtons.forEach((button) => {
			button.addEventListener("click", function (e) {
				e.preventDefault();
				const input = this.parentElement.querySelector(".qty-input");
				if (input) {
					const currentValue = parseInt(input.value) || 1;
					if (currentValue > 1) {
						input.value = currentValue - 1;
					}
				}
			});
		});

		qtyPlusButtons.forEach((button) => {
			button.addEventListener("click", function (e) {
				e.preventDefault();
				const input = this.parentElement.querySelector(".qty-input");
				if (input) {
					const currentValue = parseInt(input.value) || 1;
					input.value = currentValue + 1;
				}
			});
		});

		// ═══════════════════════════════════════════════════════════
		// ADD TO CART AJAX
		// ═══════════════════════════════════════════════════════════
		const addToCartButtons = document.querySelectorAll(".add-to-cart-ajax");

		addToCartButtons.forEach((button) => {
			button.addEventListener("click", function () {
				const productId = this.getAttribute("data-product-id");
				const qtyInput = this.closest(".actions-row").querySelector(".qty-input");
				const quantity = qtyInput ? parseInt(qtyInput.value) || 1 : 1;
				const originalText = this.textContent;

				// Disable button and show loading
				this.disabled = true;
				this.textContent = "Ajout...";

				// AJAX call
				fetch(scwShopAjax.ajaxUrl, {
					method: "POST",
					headers: {
						"Content-Type": "application/x-www-form-urlencoded",
					},
					body: new URLSearchParams({
						action: "scw_add_to_cart",
						nonce: scwShopAjax.nonce,
						product_id: productId,
						quantity: quantity,
					}),
				})
					.then((response) => response.json())
					.then((data) => {
						if (data.success) {
							// Show success state
							this.textContent = "✓ Ajouté !";
							this.classList.add("success");

							// Update cart count in header
							const cartCount = document.querySelector(".cart-count");
							if (cartCount && data.data.cart_count !== undefined) {
								cartCount.textContent = data.data.cart_count;
							}

							// Trigger WooCommerce event for fragments update
							if (typeof jQuery !== "undefined") {
								jQuery(document.body).trigger("wc_fragment_refresh");
							}

							// Reset after 2 seconds
							setTimeout(() => {
								this.textContent = originalText;
								this.classList.remove("success");
								this.disabled = false;
							}, 2000);
						} else {
							alert(data.data?.message || "Erreur lors de l'ajout au panier");
							this.textContent = originalText;
							this.disabled = false;
						}
					})
					.catch((error) => {
						console.error("Error:", error);
						this.textContent = originalText;
						this.disabled = false;
					});
			});
		});

		// ═══════════════════════════════════════════════════════════
		// FAVORITE BUTTON
		// ═══════════════════════════════════════════════════════════
		const favoriteBtn = document.querySelector(".favorite-btn");

		if (favoriteBtn) {
			favoriteBtn.addEventListener("click", function () {
				const productId = this.getAttribute("data-product-id");
				const isActive = this.classList.contains("active");
				const action = isActive ? "scw_remove_from_favorites" : "scw_add_to_favorites";

				fetch(scwShopAjax.ajaxUrl, {
					method: "POST",
					headers: {
						"Content-Type": "application/x-www-form-urlencoded",
					},
					body: new URLSearchParams({
						action: action,
						nonce: scwShopAjax.nonce,
						product_id: productId,
					}),
				})
					.then((response) => response.json())
					.then((data) => {
						if (data.success) {
							this.classList.toggle("active");
							const svg = this.querySelector("svg");
							if (svg) {
								svg.setAttribute("fill", this.classList.contains("active") ? "currentColor" : "none");
							}
						}
					})
					.catch((error) => console.error("Error:", error));
			});
		}

		// ═══════════════════════════════════════════════════════════
		// PRICE EDITOR (Reseller mode)
		// ═══════════════════════════════════════════════════════════
		const priceInput = document.querySelector(".price-input-detail");
		if (priceInput) {
			priceInput.addEventListener("input", function () {
				const newPrice = parseFloat(this.value) || 0;
				const buyPrice = parseFloat(this.getAttribute("data-buy-price")) || 0;

				// Calculate new margin
				const marginValue = newPrice - buyPrice;
				const marginPercent = newPrice > 0 ? (marginValue / newPrice) * 100 : 0;

				// Update margin display
				const marginKpis = document.querySelectorAll(".kpi");
				const marginFill = document.querySelector(".margin-fill");

				// Find the margin KPI (second one)
				if (marginKpis.length >= 2) {
					const marginValueEl = marginKpis[1].querySelector(".value");
					if (marginValueEl) {
						marginValueEl.textContent = Math.round(marginPercent) + "%";

						// Update color
						let color = "#10b981"; // green
						if (marginPercent < 15) {
							color = "#ef4444"; // red
						} else if (marginPercent < 30) {
							color = "#f59e0b"; // orange
						}
						marginValueEl.style.color = color;

						if (marginFill) {
							marginFill.style.width = Math.min(marginPercent, 100) + "%";
							marginFill.style.background = color;
						}
					}
				}
			});

			// Save price change on blur
			priceInput.addEventListener("blur", function () {
				const newPrice = parseFloat(this.value) || 0;
				const productId = this.getAttribute("data-product-id");

				if (newPrice > 0 && productId) {
					// AJAX call to save price
					fetch(scwShopAjax.ajaxUrl, {
						method: "POST",
						headers: {
							"Content-Type": "application/x-www-form-urlencoded",
						},
						body: new URLSearchParams({
							action: "update_product_price",
							nonce: scwShopAjax.nonce,
							product_id: productId,
							new_price: newPrice,
						}),
					})
						.then((response) => response.json())
						.then((data) => {
							if (!data.success) {
								console.error("Failed to update price");
							}
						})
						.catch((error) => {
							console.error("Error:", error);
						});
				}
			});
		}

		// ═══════════════════════════════════════════════════════════
		// READ MORE BUTTON for Description
		// ═══════════════════════════════════════════════════════════
		const descriptionText = document.querySelector('.product-description-short .description-text');
		const readMoreBtn = document.querySelector('.read-more-btn');

		if (descriptionText && readMoreBtn) {
			// Check if content is truncated (height > max-height)
			if (descriptionText.scrollHeight > descriptionText.clientHeight) {
				// Show the "Voir plus" button
				readMoreBtn.style.display = 'block';
			}

			readMoreBtn.addEventListener('click', function() {
				descriptionText.classList.toggle('expanded');
				if (descriptionText.classList.contains('expanded')) {
					this.textContent = 'Voir moins';
				} else {
					this.textContent = 'Voir plus';
				}
			});
		}

		// ═══════════════════════════════════════════════════════════
		// IMAGE ZOOM ON HOVER + FULLSCREEN ON CLICK
		// ═══════════════════════════════════════════════════════════
		const mainImageContainer = document.querySelector('.main-image');
		const mainImageSrc = document.getElementById('main-image-src');

		if (mainImageContainer && mainImageSrc) {
			// Zoom effect that follows mouse
			mainImageContainer.addEventListener('mouseenter', function() {
				this.classList.add('zoomed');
			});

			mainImageContainer.addEventListener('mousemove', function(e) {
				if (!this.classList.contains('zoomed')) return;

				const rect = this.getBoundingClientRect();
				const x = ((e.clientX - rect.left) / rect.width) * 100;
				const y = ((e.clientY - rect.top) / rect.height) * 100;

				mainImageSrc.style.transformOrigin = `${x}% ${y}%`;
			});

			mainImageContainer.addEventListener('mouseleave', function() {
				this.classList.remove('zoomed');
				mainImageSrc.style.transformOrigin = 'center center';
			});

			// Create fullscreen modal on click
			let fullscreenModal = null;

			mainImageContainer.addEventListener('click', function() {
				// Create modal if it doesn't exist
				if (!fullscreenModal) {
					fullscreenModal = document.createElement('div');
					fullscreenModal.className = 'image-fullscreen-modal';
					fullscreenModal.innerHTML = `
						<div class="fullscreen-image-container">
							<img src="${mainImageSrc.src}" alt="${mainImageSrc.alt}" />
							<button class="fullscreen-close-btn" aria-label="Fermer">
								<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
									<line x1="18" y1="6" x2="6" y2="18"></line>
									<line x1="6" y1="6" x2="18" y2="18"></line>
								</svg>
							</button>
						</div>
					`;
					document.body.appendChild(fullscreenModal);

					// Close button click
					const closeBtn = fullscreenModal.querySelector('.fullscreen-close-btn');
					closeBtn.addEventListener('click', function(e) {
						e.stopPropagation();
						fullscreenModal.classList.remove('active');
						document.body.style.overflow = '';
					});

					// Click on overlay to close
					fullscreenModal.addEventListener('click', function(e) {
						if (e.target === fullscreenModal) {
							fullscreenModal.classList.remove('active');
							document.body.style.overflow = '';
						}
					});

					// Escape key to close
					document.addEventListener('keydown', function(e) {
						if (e.key === 'Escape' && fullscreenModal.classList.contains('active')) {
							fullscreenModal.classList.remove('active');
							document.body.style.overflow = '';
						}
					});
				}

				// Update image src and show modal
				const fullscreenImg = fullscreenModal.querySelector('img');
				fullscreenImg.src = mainImageSrc.src;
				fullscreenImg.alt = mainImageSrc.alt;
				fullscreenModal.classList.add('active');
				document.body.style.overflow = 'hidden';
			});
		}
	});
})();
