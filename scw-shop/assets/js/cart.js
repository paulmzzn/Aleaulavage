/**
 * Cart Page JavaScript
 * Manages cart quantity updates and item removal - OPTIMISTIC UI
 *
 * @package SCW_Shop
 */

(function () {
	"use strict";

	document.addEventListener("DOMContentLoaded", function () {
		const cartContainer = document.querySelector(".cart-container");
		if (!cartContainer) return;

		// Debounce timer for AJAX calls
		const pendingUpdates = {};

		// Handle quantity buttons (plus/minus)
		document.querySelectorAll(".qty-selector").forEach((selector) => {
			const minusBtn = selector.querySelector(".qty-btn.minus");
			const plusBtn = selector.querySelector(".qty-btn.plus");
			const input = selector.querySelector(".qty-input");
			const cartItem = selector.closest(".cart-item");
			const cartItemKey = input.getAttribute("data-cart-item-key");

			// Plus button
			plusBtn?.addEventListener("click", function () {
				const currentQty = parseInt(input.value) || 1;
				const newQty = currentQty + 1;
				input.value = newQty;
				updateItemPrices(cartItem, newQty);
				updateCartTotals();
				debouncedUpdate(cartItemKey, newQty);
			});

			// Minus button
			minusBtn?.addEventListener("click", function () {
				const currentQty = parseInt(input.value) || 1;
				if (currentQty <= 1) return;
				const newQty = currentQty - 1;
				input.value = newQty;
				updateItemPrices(cartItem, newQty);
				updateCartTotals();
				debouncedUpdate(cartItemKey, newQty);
			});

			// Direct input change
			input?.addEventListener("change", function () {
				let newQty = parseInt(this.value) || 1;
				if (newQty < 1) newQty = 1;
				this.value = newQty;
				updateItemPrices(cartItem, newQty);
				updateCartTotals();
				debouncedUpdate(cartItemKey, newQty);
			});
		});

		// Handle item removal
		document.querySelectorAll(".remove-link").forEach((btn) => {
			btn.addEventListener("click", function () {
				const cartItemKey = this.getAttribute("data-cart-item-key");
				const cartItem = this.closest(".cart-item");

				// IMMEDIATE UI UPDATE - fade out and remove
				cartItem.style.transition = "opacity 0.2s, transform 0.2s";
				cartItem.style.opacity = "0";
				cartItem.style.transform = "translateX(-20px)";

				setTimeout(() => {
					cartItem.remove();
					updateCartTotals();
					updateItemCount();

					// Check if cart is empty
					const remainingItems = document.querySelectorAll(".cart-item");
					if (remainingItems.length === 0) {
						location.reload(); // Reload to show empty state
					}
				}, 200);

				// AJAX call to remove
				removeCartItem(cartItemKey);
			});
		});

		// Debounced update function
		function debouncedUpdate(cartItemKey, quantity) {
			// Clear previous pending update for this item
			if (pendingUpdates[cartItemKey]) {
				clearTimeout(pendingUpdates[cartItemKey]);
			}

			// Schedule new update (300ms delay)
			pendingUpdates[cartItemKey] = setTimeout(() => {
				updateCartQuantity(cartItemKey, quantity);
				delete pendingUpdates[cartItemKey];
			}, 300);
		}

		// Update item line prices based on new quantity
		function updateItemPrices(cartItem, newQty) {
			const unitPriceEl = cartItem.querySelector(".unit-price");
			const totalPriceEl = cartItem.querySelector(".total-price");

			if (!unitPriceEl || !totalPriceEl) return;

			// Parse unit price from text like "45,00 € HT /u"
			const unitPriceText = unitPriceEl.textContent;
			const unitPriceMatch = unitPriceText.match(/([\d\s,]+)/);
			if (!unitPriceMatch) return;

			const unitPrice = parseFloat(unitPriceMatch[1].replace(/\s/g, "").replace(",", "."));
			const newTotal = unitPrice * newQty;

			// Format and update
			totalPriceEl.textContent = formatPrice(newTotal) + " € HT";
		}

		// Update cart totals (subtotal, shipping, tax, total)
		function updateCartTotals() {
			let subtotal = 0;

			// Sum all line totals
			document.querySelectorAll(".cart-item").forEach((item) => {
				const totalPriceEl = item.querySelector(".total-price");
				if (totalPriceEl) {
					const priceText = totalPriceEl.textContent;
					const priceMatch = priceText.match(/([\d\s,]+)/);
					if (priceMatch) {
						subtotal += parseFloat(priceMatch[1].replace(/\s/g, "").replace(",", "."));
					}
				}
			});

			// Constants
			const FREE_SHIPPING_THRESHOLD = 550;
			const SHIPPING_COST = 19;
			const TAX_RATE = 0.20;

			// Calculate
			const shipping = subtotal >= FREE_SHIPPING_THRESHOLD ? 0 : SHIPPING_COST;
			const tax = subtotal * TAX_RATE; // TVA only on products, not shipping
			const totalTTC = subtotal + shipping + tax;
			const remaining = Math.max(0, FREE_SHIPPING_THRESHOLD - subtotal);
			const progressPercent = Math.min(100, (subtotal / FREE_SHIPPING_THRESHOLD) * 100);

			// Update summary lines
			const summaryLines = document.querySelectorAll(".summary-line");
			if (summaryLines[0]) {
				summaryLines[0].querySelector("span:last-child").textContent = formatPrice(subtotal) + " €";
			}
			if (summaryLines[1]) {
				summaryLines[1].querySelector("span:last-child").textContent = formatPrice(tax) + " €";
			}
			if (summaryLines[2]) {
				summaryLines[2].querySelector("span:last-child").textContent = shipping === 0 ? "Offerte" : formatPrice(shipping) + " €";
			}

			// Update total
			const bigPrice = document.querySelector(".big-price");
			if (bigPrice) {
				bigPrice.textContent = formatPrice(totalTTC) + " €";
			}

			// Update shipping progress bar
			const progressFill = document.querySelector(".progress-fill");
			const shippingMsg = document.querySelector(".shipping-msg");
			if (progressFill) {
				progressFill.style.width = progressPercent + "%";
			}
			if (shippingMsg && remaining > 0) {
				shippingMsg.innerHTML = `Ajoutez <strong>${formatPrice(remaining)} € HT</strong> pour la <strong>livraison offerte</strong>`;
			} else if (shippingMsg) {
				shippingMsg.innerHTML = `⭐ Vous bénéficiez de la <strong>livraison offerte</strong> !`;
				shippingMsg.classList.add("success");
			}

			// Update delivery cost display
			const deliveryCost = document.querySelector(".delivery-cost");
			if (deliveryCost) {
				deliveryCost.innerHTML = shipping === 0 
					? '<span class="text-green">Offerte</span>' 
					: formatPrice(shipping) + " € HT";
			}
		}

		// Update item count in header
		function updateItemCount() {
			const count = document.querySelectorAll(".cart-item").length;
			const itemCountEl = document.querySelector(".item-count");
			if (itemCountEl) {
				itemCountEl.textContent = count + " article" + (count > 1 ? "s" : "");
			}

			// Update header cart count
			const headerCount = document.querySelector(".cart-count");
			if (headerCount) {
				let totalQty = 0;
				document.querySelectorAll(".qty-input").forEach(el => {
					totalQty += parseInt(el.value) || 0;
				});
				headerCount.textContent = totalQty;
			}
		}

		// Format price helper
		function formatPrice(price) {
			return price.toFixed(2).replace(".", ",").replace(/\B(?=(\d{3})+(?!\d))/g, " ");
		}

		// AJAX: Update cart quantity (silent, no reload)
		function updateCartQuantity(cartItemKey, quantity) {
			fetch(scw_shop_ajax.ajax_url, {
				method: "POST",
				headers: { "Content-Type": "application/x-www-form-urlencoded" },
				body: new URLSearchParams({
					action: "scw_update_cart_quantity",
					nonce: scw_shop_ajax.nonce,
					cart_item_key: cartItemKey,
					quantity: quantity,
				}),
			})
			.then(r => r.json())
			.catch(console.error);
		}

		// AJAX: Remove cart item (silent, no reload)
		function removeCartItem(cartItemKey) {
			fetch(scw_shop_ajax.ajax_url, {
				method: "POST",
				headers: { "Content-Type": "application/x-www-form-urlencoded" },
				body: new URLSearchParams({
					action: "scw_remove_cart_item",
					nonce: scw_shop_ajax.nonce,
					cart_item_key: cartItemKey,
				}),
			})
			.then(r => r.json())
			.catch(console.error);
		}
	});
})();
