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

		// Step 1 form fields (required) - Now using billing fields for main section
		const billingFirstName = document.querySelector('input[name="billing_first_name"]');
		const billingLastName = document.querySelector('input[name="billing_last_name"]');
		const billingCompany = document.querySelector('input[name="billing_company"]');
		const billingSiret = document.querySelector('input[name="billing_siret"]');
		const billingAddress = document.querySelector('input[name="billing_address_1"]');
		const billingPostcode = document.querySelector('input[name="billing_postcode"]');
		const billingCity = document.querySelector('input[name="billing_city"]');

		const requiredStep1Fields = [
			billingFirstName,
			billingLastName,
			billingCompany,
			billingSiret,
			billingAddress,
			billingPostcode,
			billingCity
		];

		// Validate fields and show red borders
		function validateFields(fields) {
			let isValid = true;
			fields.forEach(field => {
				if (!field) return;
				// Skip validation for optional fields if needed, but here we want them required as per form
				// Check if field has required attribute
				if (field.hasAttribute('required') && !field.value.trim()) {
					field.classList.add('field-error');
					isValid = false;
				} else if (!field.hasAttribute('required')) {
					// Optional fields are valid empty
					field.classList.remove('field-error');
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
			// Update shipping fields when checkbox changes
			copyBillingToShippingFields();
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
				summaryAddress.textContent = `${billingAddress.value}, ${billingPostcode.value} ${billingCity.value}`;
			}
			if (summaryBillingNote) {
				summaryBillingNote.textContent = sameBillingCheckbox?.checked
					? "Livraison identique"
					: "Adresse de livraison différente";
			}

			// Copy billing data to shipping fields if needed
			copyBillingToShippingFields();

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

            // NEUTRALISE DEFAULT PAYMENT SELECTION / VISIBILITY
            // Ensure no payment option is selected visually
            document.querySelectorAll(".payment-option").forEach((opt) => {
                opt.classList.remove("selected");
            });
            // Ensure all gateway fields are hidden
            document.querySelectorAll(".gateway-fields").forEach((fields) => {
                fields.style.display = "none";
            });
            // Uncheck all radio buttons to enforce explicit selection
            paymentMethodRadios.forEach(radio => {
                radio.checked = false;
            });

			// Trigger Stripe to reinitialize its elements
			setTimeout(function() {
				// Trigger WooCommerce update_checkout event
				jQuery(document.body).trigger('update_checkout');
				
				// Also trigger a resize to help Stripe Elements recalculate
				window.dispatchEvent(new Event('resize'));
			}, 100);
		});

		// Copy billing fields to shipping fields when checkbox is checked
		function copyBillingToShippingFields() {
			// Shipping fields (same as billing or different)
			// Note: We check for element existence before assigning to avoid null errors

			const setVal = (id, val) => {
				const el = document.getElementById(id);
				if (el) el.value = val || "";
			};

			if (sameBillingCheckbox?.checked) {
				// Copy ALL billing data to shipping when checkbox is checked (addresses are same)
				setVal('hidden_shipping_first_name', billingFirstName?.value);
				setVal('hidden_shipping_last_name', billingLastName?.value);
				setVal('hidden_shipping_company', billingCompany?.value);
				setVal('hidden_shipping_siret', billingSiret?.value);
				setVal('hidden_shipping_address_1', billingAddress?.value);
				setVal('hidden_shipping_postcode', billingPostcode?.value);
				setVal('hidden_shipping_city', billingCity?.value);
			} else {
				// When addresses are different, only copy name/company/siret fields
				setVal('hidden_shipping_first_name', billingFirstName?.value);
				setVal('hidden_shipping_last_name', billingLastName?.value);
				setVal('hidden_shipping_company', billingCompany?.value);
				setVal('hidden_shipping_siret', billingSiret?.value);

				// Copy address fields from display fields to hidden fields
				const shippingAddressDisplay = document.getElementById('shipping_address_1_display');
				const shippingPostcodeDisplay = document.getElementById('shipping_postcode_display');
				const shippingCityDisplay = document.getElementById('shipping_city_display');

				setVal('hidden_shipping_address_1', shippingAddressDisplay?.value);
				setVal('hidden_shipping_postcode', shippingPostcodeDisplay?.value);
				setVal('hidden_shipping_city', shippingCityDisplay?.value);
			}
		}

		// Sync shipping display fields to hidden fields in real-time
		document.querySelectorAll('.shipping-field-display').forEach(field => {
			field.addEventListener('input', function() {
				const targetId = this.getAttribute('data-target');
				const targetField = document.getElementById(targetId);
				if (targetField) {
					targetField.value = this.value;
				}
			});
		});

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
					
					// IMMEDIATE FIX: Force display block on children too
					const hiddenElements = gatewayFields.querySelectorAll('.wc-upe-form, .wc-stripe-upe-element, fieldset');
					hiddenElements.forEach(el => {
						el.style.display = 'block';
						el.style.visibility = 'visible';
						el.style.height = 'auto';
						el.style.opacity = '1';
					});
				}
			});
		});

		// ═══════════════════════════════════════════════════════════
		// ADDRESS AUTOCOMPLETE (API adresse.data.gouv.fr)
		// ═══════════════════════════════════════════════════════════
		
		function setupAddressAutocomplete(inputId, suggestionsId, postcodeFieldId, cityFieldId) {
			const input = document.getElementById(inputId);
			const suggestionsContainer = document.getElementById(suggestionsId);
			const postcodeInput = document.getElementById(postcodeFieldId);
			const cityInput = document.getElementById(cityFieldId);
			
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
		
		// Initialize autocomplete for billing and shipping addresses
		setupAddressAutocomplete(
			"billing_address_1",
			"billing-address-suggestions",
			"billing_postcode",
			"billing_city"
		);

		setupAddressAutocomplete(
			"shipping_address_1_display",
			"shipping-address-suggestions",
			"shipping_postcode_display",
			"shipping_city_display"
		);
	});
    
    // COUPON HANDLER (Sidebar)
    document.addEventListener("DOMContentLoaded", function() {
        const applyBtn = document.getElementById('apply-coupon-btn');
        const couponInput = document.getElementById('sidebar-coupon-code');
        const msgContainer = document.getElementById('coupon-message');

        if (!applyBtn || !couponInput) return;

        applyBtn.addEventListener('click', function() {
            const code = couponInput.value.trim();
            if (!code) return;

            applyBtn.disabled = true;
            applyBtn.textContent = '...';

            // WooCommerce AJAX endpoint for applying coupon
            jQuery.ajax({
                type: 'POST',
                url: wc_checkout_params.wc_ajax_url.toString().replace('%%endpoint%%', 'apply_coupon'),
                data: {
                    security: wc_checkout_params.apply_coupon_nonce,
                    coupon_code: code
                },
                success: function(response) {
                    if (response && response.indexOf('woocommerce-error') === -1) {
                        msgContainer.innerHTML = '<span style="color:#5899e2;">Code appliqué !</span>';
                        location.reload();
                    } else {
                        let errorText = 'Code invalide';
                        try {
                             const parser = new DOMParser();
                             const doc = parser.parseFromString(response, 'text/html');
                             errorText = doc.querySelector('li')?.textContent || 'Code invalide';
                        } catch(e){}
                        msgContainer.innerHTML = '<span style="color:#ef4444;">' + errorText + '</span>';
                    }
                    applyBtn.disabled = false;
                    applyBtn.textContent = 'OK';
                },
                error: function() {
                    msgContainer.innerHTML = '<span style="color:#ef4444;">Erreur réseau</span>';
                    applyBtn.disabled = false;
                    applyBtn.textContent = 'OK';
                }
            });
        });
    });

    // UPDATE PLACE ORDER BUTTON TEXT WITH TOTAL PRICE
    document.addEventListener("DOMContentLoaded", function() {
        function updatePlaceOrderButtonText() {
            const placeOrderBtn = document.getElementById('place_order');
            if (!placeOrderBtn) return;

            // Try to find the total price from WooCommerce order review
            const totalElement = document.querySelector('.order-total .woocommerce-Price-amount, .summary-totals .total .woocommerce-Price-amount, .order-total bdi');

            if (totalElement) {
                const totalPrice = totalElement.textContent.trim();
                placeOrderBtn.textContent = 'Payer ' + totalPrice;
                placeOrderBtn.value = 'Payer ' + totalPrice;
            } else {
                // Fallback if price not found
                placeOrderBtn.textContent = 'Payer';
                placeOrderBtn.value = 'Payer';
            }
        }

        // Update on page load
        updatePlaceOrderButtonText();

        // Update when WooCommerce updates checkout (cart changes, payment method changes, etc.)
        jQuery(document.body).on('updated_checkout', function() {
            updatePlaceOrderButtonText();
        });

        // Also update after a short delay to catch any delayed renders
        setTimeout(updatePlaceOrderButtonText, 500);
    });

    // SET DEFAULT PAYMENT METHOD TO STRIPE (CARD)
    document.addEventListener("DOMContentLoaded", function() {
        function setDefaultPaymentMethod() {
            // Find the Stripe payment method radio button (card)
            const stripeRadio = document.getElementById('payment_method_stripe');

            if (stripeRadio && !stripeRadio.checked) {
                // Uncheck all payment methods
                const allRadios = document.querySelectorAll('input[name="payment_method"]');
                allRadios.forEach(radio => {
                    radio.checked = false;
                });

                // Hide all payment boxes
                const allPaymentBoxes = document.querySelectorAll('.payment_box');
                allPaymentBoxes.forEach(box => {
                    box.style.display = 'none';
                });

                // Select Stripe card
                stripeRadio.checked = true;

                // Show Stripe card payment box
                const stripePaymentBox = document.querySelector('.payment_box.payment_method_stripe');
                if (stripePaymentBox) {
                    stripePaymentBox.style.display = 'block';
                }

                // Trigger change event to ensure WooCommerce knows about the selection
                jQuery(stripeRadio).trigger('change');
            }
        }

        // Set default on page load
        setTimeout(setDefaultPaymentMethod, 100);

        // Set default after checkout update
        jQuery(document.body).on('updated_checkout', function() {
            setTimeout(setDefaultPaymentMethod, 100);
        });
    });
})();

// CHECKOUT LOADING OVERLAY
(function() {
    document.addEventListener("DOMContentLoaded", function() {
        // Create overlay HTML
        const overlay = document.createElement('div');
        overlay.className = 'checkout-loading-overlay';
        overlay.innerHTML = `
            <div class="checkout-loader">
                <div class="checkout-loader-icon">
                    <svg class="checkout-loader-lock" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M19 11H5C3.89543 11 3 11.8954 3 13V20C3 21.1046 3.89543 22 5 22H19C20.1046 22 21 21.1046 21 20V13C21 11.8954 20.1046 11 19 11Z" stroke="#5899e2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M7 11V7C7 5.67392 7.52678 4.40215 8.46447 3.46447C9.40215 2.52678 10.6739 2 12 2C13.3261 2 14.5979 2.52678 15.5355 3.46447C16.4732 4.40215 17 5.67392 17 7V11" stroke="#5899e2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="12" cy="16" r="1.5" fill="#5899e2"/>
                    </svg>
                </div>
                <div class="checkout-loader-text">Paiement en cours</div>
                <div class="checkout-loader-subtext">Merci de patienter<span class="dots"></span></div>
                <div class="checkout-loader-progress">
                    <div class="checkout-loader-progress-bar"></div>
                </div>
            </div>
        `;
        document.body.appendChild(overlay);

        // Animate dots
        let dotsInterval;
        function animateDots() {
            const dotsElement = overlay.querySelector('.dots');
            if (!dotsElement) return;

            let dots = 0;
            dotsInterval = setInterval(function() {
                dots = (dots + 1) % 4;
                dotsElement.textContent = '.'.repeat(dots);
            }, 500);
        }

        // Detect 3D Secure modal and temporarily hide overlay (optimized)
        let check3DSInterval;
        function check3DSecure() {
            if (!overlay.classList.contains('active')) {
                return;
            }

            // Check for Stripe 3D Secure modal/challenge iframe specifically
            // 3D Secure iframes are usually large and positioned over the page
            const iframes = document.querySelectorAll('iframe');
            let stripe3DSFound = false;

            for (let i = 0; i < iframes.length; i++) {
                const iframe = iframes[i];
                const name = iframe.getAttribute('name') || '';
                const src = iframe.getAttribute('src') || '';

                // Look for specific 3D Secure indicators
                if ((name.includes('__privateStripe') && name.includes('challenge')) ||
                    (src.includes('stripe') && src.includes('3ds')) ||
                    name.includes('stripe-challenge-frame')) {

                    // Check if iframe is visible and large (3DS modals are typically full-screen or large)
                    const style = window.getComputedStyle(iframe);
                    const rect = iframe.getBoundingClientRect();

                    if (style.display !== 'none' &&
                        style.visibility !== 'hidden' &&
                        iframe.offsetParent !== null &&
                        (rect.width > 300 || rect.height > 300)) {
                        stripe3DSFound = true;
                        break;
                    }
                }
            }

            if (stripe3DSFound && !overlay.classList.contains('hide-for-3ds')) {
                // 3D Secure detected, hide overlay temporarily
                overlay.classList.add('hide-for-3ds');
            } else if (!stripe3DSFound && overlay.classList.contains('hide-for-3ds')) {
                // 3D Secure closed, show overlay again
                overlay.classList.remove('hide-for-3ds');
            }
        }

        // Hide overlay on checkout error
        function hideOverlay() {
            overlay.classList.remove('active');
            if (dotsInterval) {
                clearInterval(dotsInterval);
            }
            if (check3DSInterval) {
                clearInterval(check3DSInterval);
            }
        }

        // Show overlay when place order button is clicked
        const form = document.querySelector('form.checkout');
        if (form) {
            form.addEventListener('submit', function(e) {
                // Only show overlay if form is valid
                const placeOrderBtn = document.getElementById('place_order');
                if (placeOrderBtn && !placeOrderBtn.disabled) {
                    overlay.classList.add('active');
                    animateDots();

                    // Start checking for 3D Secure
                    if (check3DSInterval) {
                        clearInterval(check3DSInterval);
                    }
                    check3DSInterval = setInterval(check3DSecure, 300);
                }
            });
        }

        jQuery(document.body).on('checkout_error', hideOverlay);

        // Hide overlay if validation fails
        jQuery(document).ajaxComplete(function(event, xhr, settings) {
            if (settings.url && settings.url.indexOf('wc-ajax=checkout') !== -1) {
                const response = xhr.responseJSON;
                if (response && (response.result === 'failure' || response.messages)) {
                    hideOverlay();
                }
            }
        });
    });
})();