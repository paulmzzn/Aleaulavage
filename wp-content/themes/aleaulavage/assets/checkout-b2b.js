/* ==========================================
   CHECKOUT B2B - JAVASCRIPT
   ========================================== */

document.addEventListener('DOMContentLoaded', function() {
    initCheckout();
});

let currentStep = 1;

function initCheckout() {
    setupStepNavigation();
    setupFormValidation();
    setupPostalCodeAutoComplete();
    setupBillingToggle();
    setupShippingCalculation();
}

/* ==========================================
   NAVIGATION ENTRE LES ÉTAPES
   ========================================== */

function nextStep(step) {
    if (validateCurrentStep()) {
        hideCurrentStep();
        showStep(step);
        updateStepIndicator(step);
        currentStep = step;
        
        // Scroll vers le haut
        document.querySelector('.checkout-b2b-container').scrollIntoView({
            behavior: 'smooth'
        });
    }
}

function prevStep(step) {
    hideCurrentStep();
    showStep(step);
    updateStepIndicator(step);
    currentStep = step;
    
    // Scroll vers le haut
    document.querySelector('.checkout-b2b-container').scrollIntoView({
        behavior: 'smooth'
    });
}

function hideCurrentStep() {
    const currentSection = document.querySelector('.checkout-section.active');
    if (currentSection) {
        currentSection.classList.remove('active');
    }
}

function showStep(step) {
    const section = document.getElementById(`step-${step}`);
    if (section) {
        section.classList.add('active');
    }
}

function updateStepIndicator(step) {
    // Retirer les classes active de tous les steps
    document.querySelectorAll('.step').forEach(s => {
        s.classList.remove('active');
        if (parseInt(s.dataset.step) < step) {
            s.classList.add('completed');
        } else {
            s.classList.remove('completed');
        }
    });
    
    // Activer le step courant
    const activeStep = document.querySelector(`.step[data-step="${step}"]`);
    if (activeStep) {
        activeStep.classList.add('active');
    }
}

/* ==========================================
   VALIDATION DES FORMULAIRES
   ========================================== */

function setupFormValidation() {
    // Validation en temps réel
    const fields = document.querySelectorAll('.form-control[required]');
    fields.forEach(field => {
        field.addEventListener('blur', () => validateField(field));
        field.addEventListener('input', () => clearError(field));
    });

    // Validation spéciale pour le SIRET
    const siretField = document.getElementById('billing_siret');
    if (siretField) {
        siretField.addEventListener('input', validateSiret);
    }

    // Validation email
    const emailField = document.getElementById('billing_email');
    if (emailField) {
        emailField.addEventListener('blur', validateEmail);
    }

    // Validation téléphone
    const phoneField = document.getElementById('billing_phone');
    if (phoneField) {
        phoneField.addEventListener('blur', validatePhone);
    }
}

function validateCurrentStep() {
    let isValid = true;
    const currentSection = document.querySelector('.checkout-section.active');
    const requiredFields = currentSection.querySelectorAll('.form-control[required]');

    requiredFields.forEach(field => {
        if (!validateField(field)) {
            isValid = false;
        }
    });

    // Validation spéciale pour l'étape 3 (CGV)
    if (currentStep === 3) {
        const termsCheckbox = document.getElementById('terms');
        if (termsCheckbox && !termsCheckbox.checked) {
            showError(termsCheckbox, 'Vous devez accepter les conditions générales de vente');
            isValid = false;
        }
    }

    if (!isValid) {
        showNotification('Veuillez corriger les erreurs avant de continuer', 'error');
    }

    return isValid;
}

function validateField(field) {
    const value = field.value.trim();
    
    // Champ requis vide
    if (field.hasAttribute('required') && !value) {
        showError(field, 'Ce champ est obligatoire');
        return false;
    }

    // Validation spécifique selon le type de champ
    switch (field.id) {
        case 'billing_siret':
            return validateSiret(field);
        case 'billing_email':
            return validateEmail(field);
        case 'billing_phone':
            return validatePhone(field);
        case 'billing_postcode':
            return validatePostalCode(field);
    }

    clearError(field);
    return true;
}

function validateSiret(field) {
    const siret = field.value.replace(/\s/g, '');
    
    if (!siret) return true; // Sera géré par la validation required
    
    // Vérifier que c'est bien 14 chiffres
    if (!/^[0-9]{14}$/.test(siret)) {
        showError(field, 'Le SIRET doit contenir exactement 14 chiffres');
        return false;
    }

    // Algorithme de validation SIRET (optionnel)
    if (!isValidSiret(siret)) {
        showError(field, 'Le numéro SIRET n\'est pas valide');
        return false;
    }

    // Formatage automatique
    field.value = siret.replace(/(\d{3})(\d{3})(\d{3})(\d{5})/, '$1 $2 $3 $4');
    
    clearError(field);
    showSuccess(field);
    return true;
}

function isValidSiret(siret) {
    // Algorithme Luhn pour validation SIRET
    let sum = 0;
    for (let i = 0; i < 14; i++) {
        let digit = parseInt(siret.charAt(i));
        if (i % 2 === 1) {
            digit *= 2;
            if (digit > 9) {
                digit -= 9;
            }
        }
        sum += digit;
    }
    return sum % 10 === 0;
}

function validateEmail(field) {
    const email = field.value.trim();
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    
    if (!email) return true; // Sera géré par required
    
    if (!emailRegex.test(email)) {
        showError(field, 'Veuillez saisir une adresse e-mail valide');
        return false;
    }
    
    clearError(field);
    showSuccess(field);
    return true;
}

function validatePhone(field) {
    const phone = field.value.trim();
    const phoneRegex = /^(?:(?:\+|00)33|0)\s*[1-9](?:[\s.-]*\d{2}){4}$/;
    
    if (!phone) return true; // Sera géré par required
    
    if (!phoneRegex.test(phone)) {
        showError(field, 'Veuillez saisir un numéro de téléphone valide');
        return false;
    }
    
    clearError(field);
    showSuccess(field);
    return true;
}

function validatePostalCode(field) {
    const postcode = field.value.trim();
    const postcodeRegex = /^[0-9]{5}$/;
    
    if (!postcode) return true; // Sera géré par required
    
    if (!postcodeRegex.test(postcode)) {
        showError(field, 'Le code postal doit contenir 5 chiffres');
        return false;
    }
    
    clearError(field);
    return true;
}

/* ==========================================
   GESTION DES ERREURS ET SUCCÈS
   ========================================== */

function showError(field, message) {
    clearError(field);
    field.classList.add('error');
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'validation-message';
    errorDiv.innerHTML = `<i class="fa fa-exclamation-circle"></i> ${message}`;
    
    field.parentNode.appendChild(errorDiv);
}

function showSuccess(field) {
    clearError(field);
    field.classList.remove('error');
    
    const successDiv = document.createElement('div');
    successDiv.className = 'validation-message success';
    successDiv.innerHTML = `<i class="fa fa-check-circle"></i> Valide`;
    
    field.parentNode.appendChild(successDiv);
}

function clearError(field) {
    field.classList.remove('error');
    const existingMessage = field.parentNode.querySelector('.validation-message');
    if (existingMessage) {
        existingMessage.remove();
    }
}

/* ==========================================
   AUTO-COMPLÉTION CODE POSTAL → VILLE
   ========================================== */

function setupPostalCodeAutoComplete() {
    const postcodeField = document.getElementById('billing_postcode');
    const cityField = document.getElementById('billing_city');

    if (postcodeField && cityField) {
        postcodeField.addEventListener('input', function() {
            const postcode = this.value.trim();
            
            if (postcode.length === 5 && /^[0-9]{5}$/.test(postcode)) {
                fetchCityFromPostcode(postcode, cityField);
            }
        });
    }
}

async function fetchCityFromPostcode(postcode, cityField) {
    try {
        // API gouvernementale française pour les codes postaux
        const response = await fetch(`https://api-adresse.data.gouv.fr/search/?q=${postcode}&type=municipality&limit=1`);
        const data = await response.json();
        
        if (data.features && data.features.length > 0) {
            const city = data.features[0].properties.city || data.features[0].properties.name;
            cityField.value = city;
            showNotification(`Ville trouvée : ${city}`, 'success');
        }
    } catch (error) {
        console.log('Impossible de récupérer la ville automatiquement');
    }
}

/* ==========================================
   GESTION DE L'ADRESSE DE FACTURATION
   ========================================== */

function setupBillingToggle() {
    const checkbox = document.getElementById('same_billing_address');
    const separateBilling = document.getElementById('separate-billing');

    if (checkbox && separateBilling) {
        checkbox.addEventListener('change', function() {
            if (this.checked) {
                separateBilling.style.display = 'none';
                // Copier les valeurs de livraison vers facturation
                copyBillingToShipping();
            } else {
                separateBilling.style.display = 'block';
            }
        });
    }
}

function copyBillingToShipping() {
    const billingFields = ['company', 'address_1', 'postcode', 'city', 'country'];
    billingFields.forEach(field => {
        const billingField = document.getElementById(`billing_${field}`);
        const shippingField = document.getElementById(`shipping_${field}`);
        
        if (billingField && shippingField) {
            shippingField.value = billingField.value;
        }
    });
}

/* ==========================================
   CALCUL DES FRAIS DE LIVRAISON
   ========================================== */

function setupShippingCalculation() {
    const shippingMethods = document.querySelectorAll('input[name="shipping_method"]');
    
    shippingMethods.forEach(method => {
        method.addEventListener('change', updateShippingCosts);
    });
}

function updateShippingCosts() {
    const selectedMethod = document.querySelector('input[name="shipping_method"]:checked');
    if (!selectedMethod) return;

    const summarySection = document.querySelector('#order_review');
    if (!summarySection) return;

    // Déclencher la mise à jour AJAX de WooCommerce
    jQuery('body').trigger('update_checkout');
    
    if (selectedMethod.value === 'pickup') {
        showNotification('Frais de livraison supprimés pour le retrait en atelier', 'success');
    } else {
        showNotification('Frais de livraison mis à jour', 'info');
    }
}

/* ==========================================
   NOTIFICATIONS
   ========================================== */

function showNotification(message, type = 'info') {
    // Supprimer les notifications existantes
    const existingNotification = document.querySelector('.checkout-notification');
    if (existingNotification) {
        existingNotification.remove();
    }

    const notification = document.createElement('div');
    notification.className = `checkout-notification ${type}`;
    
    const icon = {
        'success': 'fa-check-circle',
        'error': 'fa-exclamation-triangle', 
        'info': 'fa-info-circle'
    }[type] || 'fa-info-circle';
    
    notification.innerHTML = `
        <i class="fa ${icon}"></i>
        <span>${message}</span>
        <button class="notification-close" onclick="this.parentElement.remove()">
            <i class="fa fa-times"></i>
        </button>
    `;

    // Styles inline pour la notification
    Object.assign(notification.style, {
        position: 'fixed',
        top: '20px',
        right: '20px',
        backgroundColor: type === 'error' ? '#dc3545' : type === 'success' ? '#28a745' : '#5899E2',
        color: 'white',
        padding: '12px 16px',
        borderRadius: '8px',
        boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
        zIndex: '10000',
        display: 'flex',
        alignItems: 'center',
        gap: '12px',
        maxWidth: '400px',
        fontSize: '14px',
        fontWeight: '500'
    });

    document.body.appendChild(notification);

    // Auto-suppression après 5 secondes
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

/* ==========================================
   GESTION DU FORMULAIRE FINAL
   ========================================== */

function setupFormSubmission() {
    const checkoutForm = document.querySelector('.checkout-b2b-form');
    const placeOrderBtn = document.getElementById('place_order');

    if (checkoutForm && placeOrderBtn) {
        checkoutForm.addEventListener('submit', function(e) {
            if (!validateCurrentStep()) {
                e.preventDefault();
                return false;
            }

            // Ajouter un état de chargement
            placeOrderBtn.disabled = true;
            placeOrderBtn.classList.add('loading');
            placeOrderBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Traitement en cours...';
            
            showNotification('Traitement de votre commande en cours...', 'info');
        });
    }
}

/* ==========================================
   INITIALISATION SUPPLÉMENTAIRE
   ========================================== */

// Setup form submission après le DOM
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(setupFormSubmission, 500);
});

// Gestion des événements clavier
document.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && e.target.classList.contains('form-control')) {
        e.preventDefault();
        
        // Passer au champ suivant
        const formControls = Array.from(document.querySelectorAll('.checkout-section.active .form-control'));
        const currentIndex = formControls.indexOf(e.target);
        
        if (currentIndex >= 0 && currentIndex < formControls.length - 1) {
            formControls[currentIndex + 1].focus();
        }
    }
});

// Sauvegarde automatique dans localStorage
function saveFormProgress() {
    const formData = {};
    const formControls = document.querySelectorAll('.form-control');
    
    formControls.forEach(field => {
        if (field.name && field.value) {
            formData[field.name] = field.value;
        }
    });
    
    localStorage.setItem('checkout_progress', JSON.stringify(formData));
}

// Restauration des données sauvegardées
function restoreFormProgress() {
    const savedData = localStorage.getItem('checkout_progress');
    
    if (savedData) {
        try {
            const formData = JSON.parse(savedData);
            
            Object.keys(formData).forEach(fieldName => {
                const field = document.querySelector(`[name="${fieldName}"]`);
                if (field && !field.value) {
                    field.value = formData[fieldName];
                }
            });
        } catch (error) {
            console.log('Erreur lors de la restauration des données');
        }
    }
}

// Sauvegarde automatique toutes les 30 secondes
setInterval(saveFormProgress, 30000);

// Restaurer au chargement
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(restoreFormProgress, 1000);
});