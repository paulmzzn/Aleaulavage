/**
 * Favorites Page JavaScript
 * Manages favorites filtering, calculations, and interactions
 *
 * @package SCW_Shop
 */

(function () {
	"use strict";

	document.addEventListener("DOMContentLoaded", function () {
		const container = document.getElementById("favorites-container");
		if (!container) return;

		const emptyState = document.getElementById("favorites-empty");
		const contentState = document.getElementById("favorites-content");
		const favoritesGrid = document.getElementById("favorites-grid");
		const tabs = document.querySelectorAll(".fav-tab");
		const totalElement = document.getElementById("favorites-total");
		const addAllBtn = document.getElementById("add-all-to-cart");

		// Get all product cards
		let allProducts = [];

		function initProducts() {
			const productCards = favoritesGrid.querySelectorAll(".product-card");
			allProducts = Array.from(productCards).map((card) => {
				const categoryElement = card.querySelector(".product-category");
				const priceElement = card.querySelector(".product-price");

				return {
					element: card,
					category: categoryElement
						? categoryElement.textContent.trim()
						: "Non classé",
					price: priceElement
						? parseFloat(
								priceElement.textContent.replace(/[^\d.]/g, "")
						  )
						: 0,
				};
			});

			// Update counts and total
			updateCounts();
			updateTotal("all");

			// Check if empty
			if (allProducts.length === 0) {
				showEmptyState();
			}
		}

		// Show/hide empty state
		function showEmptyState() {
			if (emptyState && contentState) {
				emptyState.style.display = "flex";
				contentState.style.display = "none";
			}
		}

		function hideEmptyState() {
			if (emptyState && contentState) {
				emptyState.style.display = "none";
				contentState.style.display = "block";
			}
		}

		// Update category counts
		function updateCounts() {
			tabs.forEach((tab) => {
				const category = tab.getAttribute("data-category");
				const countElement = tab.querySelector(".tab-count");

				if (category === "all") {
					countElement.textContent = allProducts.length;
				} else {
					const count = allProducts.filter(
						(p) => p.category === category
					).length;
					countElement.textContent = count;
				}
			});
		}

		// Update total price
		function updateTotal(category) {
			let total = 0;

			if (category === "all") {
				total = allProducts.reduce((sum, p) => sum + p.price, 0);
			} else {
				total = allProducts
					.filter((p) => p.category === category)
					.reduce((sum, p) => sum + p.price, 0);
			}

			if (totalElement) {
				totalElement.textContent = total.toFixed(2) + " € HT";
			}
		}

		// Filter products by category
		function filterProducts(category) {
			allProducts.forEach((product) => {
				if (category === "all" || product.category === category) {
					product.element.style.display = "block";
				} else {
					product.element.style.display = "none";
				}
			});

			updateTotal(category);
		}

		// Tab click handler
		tabs.forEach((tab) => {
			tab.addEventListener("click", function () {
				const category = this.getAttribute("data-category");

				// Update active state
				tabs.forEach((t) => t.classList.remove("active"));
				this.classList.add("active");

				// Filter products
				filterProducts(category);
			});
		});

		// Add all to cart button
		if (addAllBtn) {
			addAllBtn.addEventListener("click", function () {
				// Get visible product IDs
				const visibleProducts = allProducts.filter(
					(p) => p.element.style.display !== "none"
				);

				if (visibleProducts.length === 0) {
					alert("Aucun produit à ajouter au panier.");
					return;
				}

				// In a real implementation, you would add products to cart via AJAX
				const productIds = visibleProducts.map((p) =>
					p.element.getAttribute("data-product-id")
				);

				console.log("Adding to cart:", productIds);

				// Show success message
				alert(
					visibleProducts.length +
						" produit(s) ajouté(s) au panier !"
				);

				// TODO: Implement actual add to cart via AJAX
			});
		}

		// Initialize
		initProducts();
	});
})();
