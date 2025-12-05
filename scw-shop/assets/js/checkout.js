/**
 * Checkout Page JavaScript
 * Handles step navigation and payment method switching
 *
 * @package SCW_Shop
 */

(function () {
	"use strict";

	document.addEventListener("DOMContentLoaded", function () {
		const checkoutContainer = document.querySelector(".checkout-container");
		if (!checkoutContainer) return;

		// Elements
		const step1 = document.querySelector('[data-step="1"]');
		const step2 = document.querySelector('[data-step="2"]');
		const btnToStep2 = document.getElementById("btn-to-step-2");
		const btnEditStep1 = document.getElementById("btn-edit-step-1");
		const sameBillingCheckbox = document.getElementById("same-billing");
		const billingForm = document.getElementById("billing-form");
		const paymentOptions = document.querySelectorAll('input[name="payment_method"]');
		const cardForm = document.getElementById("card-form");
		const lcrInfo = document.getElementById("lcr-info");
		const btnPay = document.getElementById("btn-pay");

		// Step 1 form fields (required)
		const shippingFirstName = document.querySelector('input[name="shipping_first_name"]');
		const shippingLastName = document.querySelector('input[name="shipping_last_name"]');
		const shippingCompany = document.querySelector('input[name="shipping_company"]');
		const shippingSiret = document.querySelector('input[name="shipping_siret"]');
		const shippingAddress = document.querySelector('input[name="shipping_address_1"]');
		const shippingPostcode = document.querySelector('input[name="shipping_postcode"]');
		const shippingCity = document.querySelector('input[name="shipping_city"]');

		const requiredStep1Fields = [
			shippingFirstName,
			shippingLastName,
			shippingCompany,
			shippingSiret,
			shippingAddress,
			shippingPostcode,
			shippingCity
		];

		// Validate fields and show red borders
		function validateFields(fields) {
			let isValid = true;
			fields.forEach(field => {
				if (!field) return;
				if (!field.value.trim()) {
					field.classList.add('field-error');
					isValid = false;
				} else {
					field.classList.remove('field-error');
				}
			});
			return isValid;
		}

		// Remove error on input
		requiredStep1Fields.forEach(field => {
			field?.addEventListener('input', function() {
				if (this.value.trim()) {
					this.classList.remove('field-error');
				}
			});
		});

		// Summary elements
		const summaryAddress = document.getElementById("summary-address");
		const summaryBillingNote = document.getElementById("summary-billing-note");

		// Toggle billing form visibility
		sameBillingCheckbox?.addEventListener("change", function () {
			if (this.checked) {
				billingForm.style.display = "none";
			} else {
				billingForm.style.display = "block";
			}
		});

		// Go to Step 2
		btnToStep2?.addEventListener("click", function () {
			// Validate required fields with red borders
			if (!validateFields(requiredStep1Fields)) {
				// Scroll to first error
				const firstError = document.querySelector('.field-error');
				if (firstError) {
					firstError.focus();
				}
				return;
			}

			// Update summary
			if (summaryAddress) {
				summaryAddress.textContent = `${shippingAddress.value}, ${shippingPostcode.value} ${shippingCity.value}`;
			}
			if (summaryBillingNote) {
				summaryBillingNote.textContent = sameBillingCheckbox?.checked
					? "Facturation identique"
					: "Adresse de facturation différente";
			}

			// Copy shipping data to hidden fields for WooCommerce form
			copyShippingToHiddenFields();

			// Switch step states
			step1.classList.remove("active");
			step1.classList.add("completed");
			step1.querySelector(".step-content").style.display = "none";
			step1.querySelector(".step-summary").style.display = "flex";
			step1.querySelector(".step-number").textContent = "✓";

			step2.classList.add("active");
			const step2Content = document.getElementById("step2-content");
			if (step2Content) {
				step2Content.classList.remove("step-hidden");
				step2Content.classList.add("step-visible");
			}
			
			// Trigger Stripe to reinitialize its elements
			setTimeout(function() {
				// Trigger WooCommerce update_checkout event
				jQuery(document.body).trigger('update_checkout');
				
				// Also trigger a resize to help Stripe Elements recalculate
				window.dispatchEvent(new Event('resize'));
			}, 100);
		});

		// Copy shipping fields to hidden WooCommerce fields
		function copyShippingToHiddenFields() {
			// Shipping fields
			document.getElementById("hidden_shipping_first_name").value = shippingFirstName?.value || "";
			document.getElementById("hidden_shipping_last_name").value = shippingLastName?.value || "";
			document.getElementById("hidden_shipping_company").value = shippingCompany?.value || "";
			document.getElementById("hidden_shipping_address_1").value = shippingAddress?.value || "";
			document.getElementById("hidden_shipping_postcode").value = shippingPostcode?.value || "";
			document.getElementById("hidden_shipping_city").value = shippingCity?.value || "";

			// Billing fields (same as shipping or different)
			if (sameBillingCheckbox?.checked) {
				document.getElementById("hidden_billing_first_name").value = shippingFirstName?.value || "";
				document.getElementById("hidden_billing_last_name").value = shippingLastName?.value || "";
				document.getElementById("hidden_billing_company").value = shippingCompany?.value || "";
				document.getElementById("hidden_billing_address_1").value = shippingAddress?.value || "";
				document.getElementById("hidden_billing_postcode").value = shippingPostcode?.value || "";
				document.getElementById("hidden_billing_city").value = shippingCity?.value || "";
			} else {
				const billingAddress = document.querySelector('input[name="billing_address_1"]:not([type="hidden"])');
				const billingPostcode = document.querySelector('input[name="billing_postcode"]:not([type="hidden"])');
				const billingCity = document.querySelector('input[name="billing_city"]:not([type="hidden"])');
				
				document.getElementById("hidden_billing_first_name").value = shippingFirstName?.value || "";
				document.getElementById("hidden_billing_last_name").value = shippingLastName?.value || "";
				document.getElementById("hidden_billing_company").value = shippingCompany?.value || "";
				document.getElementById("hidden_billing_address_1").value = billingAddress?.value || "";
				document.getElementById("hidden_billing_postcode").value = billingPostcode?.value || "";
				document.getElementById("hidden_billing_city").value = billingCity?.value || "";
			}
		}

		// Edit Step 1
		btnEditStep1?.addEventListener("click", function () {
			step1.classList.add("active");
			step1.classList.remove("completed");
			step1.querySelector(".step-content").style.display = "block";
			step1.querySelector(".step-summary").style.display = "none";
			step1.querySelector(".step-number").textContent = "1";

			step2.classList.remove("active");
			const step2Content = document.getElementById("step2-content");
			if (step2Content) {
				step2Content.classList.add("step-hidden");
				step2Content.classList.remove("step-visible");
			}
		});

		// Payment method switching (WooCommerce gateways)
		const paymentMethodRadios = document.querySelectorAll('input[name="payment_method"]');
		paymentMethodRadios.forEach((radio) => {
			radio.addEventListener("change", function () {
				// Update selected state on labels
				document.querySelectorAll(".payment-option").forEach((opt) => {
					opt.classList.remove("selected");
				});
				this.closest(".payment-option")?.classList.add("selected");

				// Show/hide gateway-specific fields
				document.querySelectorAll(".gateway-fields").forEach((fields) => {
					fields.style.display = "none";
				});
				const gatewayFields = document.getElementById("gateway-fields-" + this.value);
				if (gatewayFields) {
					gatewayFields.style.display = "block";
				}
			});
		});

		// Form submission - trigger WooCommerce checkout
		const checkoutForm = document.getElementById("scw-checkout-form");
		checkoutForm?.addEventListener("submit", function(e) {
			e.preventDefault();
			
			// Ensure hidden fields are up to date
			copyShippingToHiddenFields();
			
			const submitBtn = this.querySelector(".btn-pay");
			const selectedPayment = document.querySelector('input[name="payment_method"]:checked')?.value;
			
			// Show loading state
			if (submitBtn) {
				submitBtn.disabled = true;
				submitBtn.textContent = "Traitement en cours...";
			}
			
			// Trigger WooCommerce checkout via their AJAX system
			const $form = jQuery(this);
			
			// For Stripe, trigger the checkout_place_order event which Stripe listens to
			const eventResult = $form.triggerHandler('checkout_place_order_' + selectedPayment);
			
			// If the event handler returned false (Stripe is handling it), stop here
			if (eventResult === false) {
				return false;
			}
			
			// Otherwise, submit via WooCommerce AJAX checkout
			jQuery.ajax({
				type: 'POST',
				url: wc_checkout_params?.checkout_url || '/?wc-ajax=checkout',
				data: $form.serialize(),
				dataType: 'json',
				success: function(result) {
					try {
						if (result.result === 'success') {
							if (result.redirect) {
								window.location.href = result.redirect;
							}
						} else if (result.result === 'failure') {
							// Show error
							let errorMsg = result.messages || 'Une erreur est survenue.';
							
							// Try to extract text from HTML
							if (errorMsg.includes('<')) {
								const div = document.createElement('div');
								div.innerHTML = errorMsg;
								errorMsg = div.textContent || div.innerText;
							}
							
							alert(errorMsg);
							
							if (submitBtn) {
								submitBtn.disabled = false;
								submitBtn.textContent = submitBtn.getAttribute('data-original-text') || 'Payer';
							}
						}
					} catch (err) {
						console.error(err);
					}
				},
				error: function(xhr, status, error) {
					console.error('Checkout error:', error);
					alert('Erreur de connexion. Veuillez réessayer.');
					
					if (submitBtn) {
						submitBtn.disabled = false;
						submitBtn.textContent = submitBtn.getAttribute('data-original-text') || 'Payer';
					}
				}
			});
			
			return false;
		});

		// ═══════════════════════════════════════════════════════════
		// ADDRESS AUTOCOMPLETE (API adresse.data.gouv.fr)
		// ═══════════════════════════════════════════════════════════
		
		function setupAddressAutocomplete(inputId, suggestionsId, postcodeField, cityField) {
			const input = document.getElementById(inputId);
			const suggestionsContainer = document.getElementById(suggestionsId);
			const postcodeInput = document.querySelector(`input[name="${postcodeField}"]`);
			const cityInput = document.querySelector(`input[name="${cityField}"]`);
			
			if (!input || !suggestionsContainer) return;
			
			let debounceTimer;
			let currentFocus = -1;
			
			// Input event - fetch suggestions
			input.addEventListener("input", function() {
				const query = this.value.trim();
				
				clearTimeout(debounceTimer);
				
				if (query.length < 3) {
					suggestionsContainer.classList.remove("active");
					suggestionsContainer.innerHTML = "";
					return;
				}
				
				debounceTimer = setTimeout(() => {
					fetchAddressSuggestions(query, suggestionsContainer, input, postcodeInput, cityInput);
				}, 300);
			});
			
			// Keyboard navigation
			input.addEventListener("keydown", function(e) {
				const items = suggestionsContainer.querySelectorAll(".address-suggestion-item");
				
				if (e.key === "ArrowDown") {
					e.preventDefault();
					currentFocus++;
					if (currentFocus >= items.length) currentFocus = 0;
					setActiveSuggestion(items, currentFocus);
				} else if (e.key === "ArrowUp") {
					e.preventDefault();
					currentFocus--;
					if (currentFocus < 0) currentFocus = items.length - 1;
					setActiveSuggestion(items, currentFocus);
				} else if (e.key === "Enter") {
					e.preventDefault();
					if (currentFocus > -1 && items[currentFocus]) {
						items[currentFocus].click();
					}
				} else if (e.key === "Escape") {
					suggestionsContainer.classList.remove("active");
					currentFocus = -1;
				}
			});
			
			// Close suggestions on click outside
			document.addEventListener("click", function(e) {
				if (!input.contains(e.target) && !suggestionsContainer.contains(e.target)) {
					suggestionsContainer.classList.remove("active");
					currentFocus = -1;
				}
			});
		}
		
		function setActiveSuggestion(items, index) {
			items.forEach((item, i) => {
				item.classList.toggle("active", i === index);
				if (i === index) {
					item.style.background = "#f1f5f9";
				} else {
					item.style.background = "";
				}
			});
		}
		
		async function fetchAddressSuggestions(query, container, input, postcodeInput, cityInput) {
			try {
				const response = await fetch(
					`https://api-adresse.data.gouv.fr/search/?q=${encodeURIComponent(query)}&limit=5&type=housenumber`
				);
				const data = await response.json();
				
				if (data.features && data.features.length > 0) {
					displaySuggestions(data.features, container, input, postcodeInput, cityInput);
				} else {
					// Try without type restriction for street-only results
					const response2 = await fetch(
						`https://api-adresse.data.gouv.fr/search/?q=${encodeURIComponent(query)}&limit=5`
					);
					const data2 = await response2.json();
					
					if (data2.features && data2.features.length > 0) {
						displaySuggestions(data2.features, container, input, postcodeInput, cityInput);
					} else {
						container.classList.remove("active");
					}
				}
			} catch (error) {
				console.error("Address API error:", error);
				container.classList.remove("active");
			}
		}
		
		function displaySuggestions(features, container, input, postcodeInput, cityInput) {
			container.innerHTML = "";
			
			features.forEach(feature => {
				const props = feature.properties;
				const item = document.createElement("div");
				item.className = "address-suggestion-item";
				
				item.innerHTML = `
					<div class="suggestion-main">${props.name || props.label}</div>
					<div class="suggestion-secondary">${props.postcode} ${props.city}</div>
				`;
				
				item.addEventListener("click", function() {
					// Fill address field
					input.value = props.name || props.housenumber + " " + props.street;
					
					// Fill postcode and city
					if (postcodeInput) postcodeInput.value = props.postcode || "";
					if (cityInput) cityInput.value = props.city || "";
					
					// Remove error states if any
					input.classList.remove("field-error");
					if (postcodeInput) postcodeInput.classList.remove("field-error");
					if (cityInput) cityInput.classList.remove("field-error");
					
					container.classList.remove("active");
				});
				
				container.appendChild(item);
			});
			
			container.classList.add("active");
		}
		
		// Initialize autocomplete for shipping and billing addresses
		setupAddressAutocomplete(
			"shipping-address-input", 
			"shipping-address-suggestions",
			"shipping_postcode",
			"shipping_city"
		);
		
		setupAddressAutocomplete(
			"billing-address-input", 
			"billing-address-suggestions",
			"billing_postcode",
			"billing_city"
		);
	});
})();
