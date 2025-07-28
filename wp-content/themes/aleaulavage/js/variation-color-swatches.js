document.addEventListener('DOMContentLoaded', function() {
  // Stocker le prix par défaut du header au chargement
  var defaultPrice = null;
  var priceHeader = document.querySelector('.purchase-header .price');
  if (priceHeader) {
    defaultPrice = priceHeader.innerHTML;
  }
  document.querySelectorAll('.variations_form').forEach(function(form) {
    form.querySelectorAll('select').forEach(function(select) {
      const label = select.closest('tr').querySelector('label');
      if (!label) return;
      const attrName = label.textContent.trim().toLowerCase();
      if (attrName === 'couleur' || attrName === 'couleurs' || attrName === 'color' || attrName === 'colors') {
        // Hide the select
        select.style.display = 'none';
        // Build swatch container
        const swatchContainer = document.createElement('div');
        swatchContainer.className = 'color-swatches';
        Array.from(select.options).forEach(function(option) {
          if (!option.value) return;
          const swatch = document.createElement('button');
          swatch.type = 'button';
          swatch.className = 'color-swatch';
          swatch.title = option.text;
          swatch.setAttribute('data-value', option.value);
          // Use color map if possible, else fallback to value
          const colorKey = option.text.trim().toLowerCase();
          swatch.style.background = COLOR_MAP[colorKey] || option.value;
          if (option.selected) swatch.classList.add('selected');
          swatch.addEventListener('click', function() {
            select.value = option.value;
            select.dispatchEvent(new Event('change', {bubbles:true}));
            swatchContainer.querySelectorAll('.color-swatch').forEach(btn => btn.classList.remove('selected'));
            swatch.classList.add('selected');
            updateColorBadge(swatch.style.background, swatch.title);
            
            // Show clear button when selection is made
            showClearButton(form);
          });
          swatchContainer.appendChild(swatch);
        });
        // Insert swatches after the label, on a new line
        const br = document.createElement('br');
        label.parentNode.insertBefore(br, label.nextSibling);
        label.parentNode.insertBefore(swatchContainer, br.nextSibling);
      } else {
        // Handle all other attributes with text buttons
        createAttributeButtons(select, label, attrName, form);
      }
    });
  });
  setupSwatchBadgeSync();
  
  // Setup global clear button for all variations
  document.querySelectorAll('.variations_form').forEach(function(form) {
    setupGlobalClearButton(form);
  });
  
  // Setup wishlist button toggle
  setupWishlistButton();
});

function updateColorBadge(color, label) {
  const badge = document.getElementById('selected-color-badge');
  if (!badge) return;
  const dot = badge.querySelector('.color-dot');
  if (color) {
    dot.style.background = color;
    dot.title = label || '';
    badge.style.display = 'flex';
  } else {
    badge.style.display = 'none';
  }
}

// Hook swatch click to badge update
function setupSwatchBadgeSync() {
  document.querySelectorAll('.color-swatches').forEach(function(container) {
    container.querySelectorAll('.color-swatch').forEach(function(swatch) {
      swatch.addEventListener('click', function() {
        updateColorBadge(swatch.style.background, swatch.title);
      });
      // Set initial badge if selected
      if (swatch.classList.contains('selected')) {
        updateColorBadge(swatch.style.background, swatch.title);
      }
    });
  });
}

// Function to create attribute buttons for non-color attributes
function createAttributeButtons(select, label, attrName, form) {
  // Hide the select
  select.style.display = 'none';
  
  // Create button container
  const buttonContainer = document.createElement('div');
  buttonContainer.className = 'attribute-buttons';
  buttonContainer.setAttribute('data-attribute', attrName);
  
  // Style du conteneur
  buttonContainer.style.cssText = `
    display: flex !important;
    flex-wrap: wrap !important;
    gap: 8px !important;
    margin: 12px 0 18px 0 !important;
    max-width: 100% !important;
    overflow: hidden !important;
  `;
  
  // Create buttons for each option
  Array.from(select.options).forEach(function(option) {
    if (!option.value) return;
    
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'attribute-button';
    button.textContent = option.text;
    button.setAttribute('data-value', option.value);
    button.title = option.text;
    
    // Styles inline pour garantir l'application
    button.style.cssText = `
      padding: 12px 20px !important;
      border: 2px solid #e3e5e8 !important;
      border-radius: 8px !important;
      background: linear-gradient(135deg, #fff 0%, #fafbfc 100%) !important;
      color: #0E2141 !important;
      font-size: 15px !important;
      font-weight: 600 !important;
      cursor: pointer !important;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
      outline: none !important;
      min-height: 44px !important;
      min-width: 60px !important;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      position: relative !important;
      box-shadow: 0 2px 6px rgba(0,0,0,0.08) !important;
      text-transform: capitalize !important;
      letter-spacing: 0.3px !important;
      text-decoration: none !important;
      margin: 0 !important;
      font-family: inherit !important;
    `;
    
    if (option.selected) button.classList.add('selected');
    
    // Gestion des états hover et selected
    button.addEventListener('mouseenter', function() {
      if (!this.classList.contains('selected')) {
        this.style.borderColor = '#f1bb69';
        this.style.background = 'linear-gradient(135deg, #fdf8f1 0%, #fcf4e8 100%)';
        this.style.transform = 'translateY(-1px)';
        this.style.boxShadow = '0 4px 12px rgba(241, 187, 105, 0.25)';
      }
    });
    
    button.addEventListener('mouseleave', function() {
      if (!this.classList.contains('selected')) {
        this.style.borderColor = '#e3e5e8';
        this.style.background = 'linear-gradient(135deg, #fff 0%, #fafbfc 100%)';
        this.style.transform = 'translateY(0)';
        this.style.boxShadow = '0 2px 6px rgba(0,0,0,0.08)';
      }
    });
    
    button.addEventListener('click', function() {
      // Don't allow clicking disabled buttons
      if (button.hasAttribute('data-disabled')) return;
      
      select.value = option.value;
      select.dispatchEvent(new Event('change', {bubbles: true}));
      
      // Update visual state - reset all buttons
      buttonContainer.querySelectorAll('.attribute-button').forEach(btn => {
        btn.classList.remove('selected');
        btn.style.borderColor = '#e3e5e8';
        btn.style.background = 'linear-gradient(135deg, #fff 0%, #fafbfc 100%)';
        btn.style.transform = 'translateY(0)';
        btn.style.boxShadow = '0 2px 6px rgba(0,0,0,0.08)';
        btn.style.fontWeight = '600';
      });
      
      // Style selected button
      button.classList.add('selected');
      button.style.borderColor = '#f1bb69';
      button.style.background = 'linear-gradient(135deg, #f1bb69 0%, #e29a3d 100%)';
      button.style.transform = 'translateY(-1px)';
      button.style.boxShadow = '0 6px 16px rgba(241, 187, 105, 0.35)';
      button.style.fontWeight = '700';
      
      
      // Update availability of other attributes
      setTimeout(() => updateAttributeAvailability(form), 100);
      
      // Show clear button when selection is made
      showClearButton(form);
    });
    
    buttonContainer.appendChild(button);
  });
  
  // Insert after label
  const br = document.createElement('br');
  label.parentNode.insertBefore(br, label.nextSibling);
  label.parentNode.insertBefore(buttonContainer, br.nextSibling);
  
}

// Simplified function to update attribute availability based on stock
function updateAttributeAvailability(form) {
  console.log('=== UPDATING ATTRIBUTE AVAILABILITY ===');
  
  // Get variation data from form
  const variationData = form.getAttribute('data-product_variations');
  if (!variationData) {
    console.log('❌ No variation data found');
    return;
  }
  
  let variations = [];
  try {
    variations = JSON.parse(variationData);
    console.log('✅ Found', variations.length, 'variations');
  } catch (e) {
    console.log('❌ Failed to parse variation data');
    return;
  }
  
  // Get current form values
  const formData = new FormData(form);
  console.log('📋 Current form selections:');
  for (let [key, value] of formData.entries()) {
    if (key.startsWith('attribute_') && value) {
      console.log(`  ${key}: ${value}`);
    }
  }
  
  // Process each attribute
  const selects = form.querySelectorAll('select[name^="attribute_"]');
  selects.forEach(function(select) {
    const attrName = select.name;
    
    // For colors, look for color swatches
    if (attrName === 'attribute_couleur') {
      const colorSwatches = form.querySelectorAll('.color-swatch');
      console.log(`🎨 Processing ${colorSwatches.length} color swatches`);
      
      colorSwatches.forEach(function(swatch) {
        const colorValue = swatch.getAttribute('data-value');
        const isAvailable = checkAvailability(variations, formData, attrName, colorValue);
        
        console.log(`🎨 ${colorValue}: ${isAvailable ? '✅' : '❌'}`);
        
        if (!isAvailable) {
          swatch.style.opacity = '0.4';
          swatch.style.cursor = 'not-allowed';
          swatch.style.filter = 'grayscale(70%)';
          swatch.setAttribute('data-disabled', 'true');
        } else {
          swatch.style.opacity = '1';
          swatch.style.cursor = 'pointer';
          swatch.style.filter = 'none';
          swatch.removeAttribute('data-disabled');
        }
      });
    }
    
    // For other attributes, look for attribute buttons
    else {
      const buttonContainer = select.parentNode.querySelector('.attribute-buttons');
      if (buttonContainer) {
        const buttons = buttonContainer.querySelectorAll('.attribute-button');
        console.log(`🔘 Processing ${buttons.length} buttons for ${attrName}`);
        
        buttons.forEach(function(button) {
          const buttonValue = button.getAttribute('data-value');
          const isAvailable = checkAvailability(variations, formData, attrName, buttonValue);
          
          console.log(`🔘 ${buttonValue}: ${isAvailable ? '✅' : '❌'}`);
          
          if (!isAvailable) {
            button.style.opacity = '0.4';
            button.style.cursor = 'not-allowed';
            button.style.background = '#f0f0f0';
            button.style.borderColor = '#ccc';
            button.style.color = '#888';
            button.setAttribute('data-disabled', 'true');
          } else {
            button.style.opacity = '1';
            button.style.cursor = 'pointer';
            button.style.background = 'linear-gradient(135deg, #fff 0%, #fafbfc 100%)';
            button.style.borderColor = '#e3e5e8';
            button.style.color = '#0E2141';
            button.removeAttribute('data-disabled');
          }
        });
      }
    }
  });
}

// Helper function to check if a specific combination is available
function checkAvailability(variations, currentFormData, testAttrName, testValue) {
  return variations.some(function(variation) {
    if (!variation.variation_is_active || !variation.is_in_stock) {
      return false;
    }
    
    // Create test combination
    const testCombination = {};
    for (let [key, value] of currentFormData.entries()) {
      if (key.startsWith('attribute_') && value) {
        testCombination[key] = value;
      }
    }
    testCombination[testAttrName] = testValue;
    
    // Check if variation matches test combination
    return Object.keys(testCombination).every(function(attrKey) {
      const varValue = variation.attributes[attrKey];
      const testVal = testCombination[attrKey];
      return !varValue || varValue === testVal;
    });
  });
}

// Initialize availability checking when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
  console.log('🚀 Initializing availability checker');
  
  setTimeout(function() {
    const forms = document.querySelectorAll('.variations_form');
    console.log('📝 Found', forms.length, 'variation forms');
    
    forms.forEach(function(form) {
      // Initial check
      updateAttributeAvailability(form);
      
      // Listen for any changes in the form
      form.addEventListener('change', function() {
        console.log('🔄 Form changed, updating availability');
        setTimeout(() => updateAttributeAvailability(form), 100);
      });
    });
  }, 1500); // Wait for WooCommerce to initialize
});

// Function to setup the global clear button for all variations
function setupGlobalClearButton(form) {
  let clearBtn = form.querySelector('.reset_variations');
  if (!clearBtn || clearBtn.hasAttribute('data-restyled')) return;
  
  // Mark as processed
  clearBtn.setAttribute('data-restyled', 'true');
  
  // Change text and style
  clearBtn.innerHTML = '<i class="fa fa-times-circle"></i> Effacer toutes les sélections';
  clearBtn.style.cssText = `
    background: #f8f9fa !important;
    color: #6c757d !important;
    border: 1px solid #dee2e6 !important;
    border-radius: 6px !important;
    padding: 8px 16px !important;
    font-size: 14px !important;
    font-weight: 500 !important;
    cursor: pointer !important;
    transition: all 0.2s !important;
    text-decoration: underline !important;
    display: none !important;
    align-items: center !important;
    gap: 6px !important;
    margin: 0 !important;
    width: auto !important;
  `;
  
  // Hover effects
  clearBtn.addEventListener('mouseenter', function() {
    this.style.background = '#e9ecef';
    this.style.color = '#495057';
    this.style.borderColor = '#adb5bd';
  });
  
  clearBtn.addEventListener('mouseleave', function() {
    this.style.background = '#f8f9fa';
    this.style.color = '#6c757d';
    this.style.borderColor = '#dee2e6';
  });
  
  // Create container but keep it hidden initially
  const variationsTable = form.querySelector('table.variations');
  if (variationsTable) {
    const container = document.createElement('div');
    container.className = 'clear-button-container';
    container.style.cssText = 'text-align: center; margin: 0; height: 0; overflow: hidden; transition: all 0.2s;';
    container.appendChild(clearBtn);
    variationsTable.insertAdjacentElement('afterend', container);
    
    // Store container reference for showing/hiding
    form.clearButtonContainer = container;
  }
  
  // Enhanced clear functionality
  clearBtn.addEventListener('click', function(e) {
    e.preventDefault();
    
    // Reset all color swatches
    form.querySelectorAll('.color-swatch').forEach(swatch => {
      swatch.classList.remove('selected');
    });
    
    // Reset all attribute buttons
    form.querySelectorAll('.attribute-button').forEach(btn => {
      btn.classList.remove('selected');
      btn.removeAttribute('data-disabled');
      btn.style.opacity = '1';
      btn.style.cursor = 'pointer';
      btn.style.background = 'linear-gradient(135deg, #fff 0%, #fafbfc 100%)';
      btn.style.borderColor = '#e3e5e8';
      btn.style.color = '#0E2141';
      btn.style.boxShadow = '0 2px 6px rgba(0,0,0,0.08)';
    });
    
    // Reset all selects
    form.querySelectorAll('select[name^="attribute_"]').forEach(select => {
      select.value = '';
      select.dispatchEvent(new Event('change', {bubbles: true}));
    });
    
    // Hide color badge
    const badge = document.getElementById('selected-color-badge');
    if (badge) badge.style.display = 'none';
    
    // Reset price to default
    setTimeout(function() {
      const priceHeader = document.querySelector('.purchase-header .price');
      if (priceHeader && defaultPrice) {
        priceHeader.innerHTML = defaultPrice;
      }
      
      // Hide variation block
      const singleVar = document.querySelector('.single_variation');
      if (singleVar) {
        singleVar.style.display = 'none';
        singleVar.innerHTML = '';
      }
    }, 50);
    
    // Hide clear button after reset
    hideClearButton(form);
  });
}

// Function to show clear button when selections are made
function showClearButton(form) {
  if (form.clearButtonContainer) {
    const container = form.clearButtonContainer;
    const button = container.querySelector('.reset_variations');
    
    container.style.height = 'auto';
    container.style.margin = '5px 0 0 0';
    container.style.overflow = 'visible';
    button.style.display = 'inline-flex';
  }
}

// Function to hide clear button when no selections
function hideClearButton(form) {
  if (form.clearButtonContainer) {
    const container = form.clearButtonContainer;
    const button = container.querySelector('.reset_variations');
    
    container.style.height = '0';
    container.style.margin = '0';
    container.style.overflow = 'hidden';
    button.style.display = 'none';
  }
}

// Function to setup wishlist button toggle functionality
function setupWishlistButton() {
  document.querySelectorAll('.wishlist-btn').forEach(function(wishlistBtn) {
    const heartIcon = wishlistBtn.querySelector('i');
    if (!heartIcon) return;
    
    const productId = wishlistBtn.getAttribute('data-product-id');
    if (!productId) return;
    
    // Vérifier que les données AJAX sont disponibles
    if (typeof wishlist_ajax === 'undefined') {
      console.log('Wishlist AJAX not available');
      return;
    }
    
    // S'assurer que l'icône a les bonnes classes
    if (!heartIcon.classList.contains('fa-heart')) {
      heartIcon.classList.add('fa-heart');
    }
    
    wishlistBtn.addEventListener('click', function(e) {
      e.preventDefault();
      
      // Vérifier si l'utilisateur est connecté
      if (!wishlist_ajax.is_logged_in) {
        showLoginModal();
        return;
      }
      
      const isActive = this.classList.contains('active');
      const action = isActive ? 'remove_from_wishlist' : 'add_to_wishlist';
      
      // Créer les données pour la requête AJAX
      const formData = new FormData();
      formData.append('action', action);
      formData.append('product_id', productId);
      formData.append('nonce', wishlist_ajax.nonce);
      
      // Désactiver le bouton temporairement
      wishlistBtn.style.pointerEvents = 'none';
      wishlistBtn.style.opacity = '0.7';
      
      // Envoyer la requête AJAX
      fetch(wishlist_ajax.ajax_url, {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        console.log('Réponse AJAX wishlist:', data); // Debug
        
        if (data.success) {
          // Basculer l'état visuel
          if (data.data && data.data.action === 'added') {
            this.classList.add('active');
            heartIcon.classList.remove('fa-regular');
            heartIcon.classList.add('fa-solid');
            console.log('Produit ajouté aux favoris');
          } else if (data.data && data.data.action === 'removed') {
            this.classList.remove('active');
            heartIcon.classList.remove('fa-solid');
            heartIcon.classList.add('fa-regular');
            console.log('Produit retiré des favoris');
          }
          
          // Afficher le message de succès (optionnel)
          if (data.data && data.data.message) {
            console.log(data.data.message);
          }
        } else {
          // Gérer les erreurs
          const errorMessage = (data.data && data.data.message) ? data.data.message : 'Erreur inconnue';
          console.error('Erreur wishlist:', errorMessage);
          alert(errorMessage);
        }
      })
      .catch(error => {
        console.error('Erreur AJAX:', error);
        alert('Erreur de connexion. Veuillez réessayer.');
      })
      .finally(() => {
        // Réactiver le bouton
        wishlistBtn.style.pointerEvents = 'auto';
        wishlistBtn.style.opacity = '1';
      });
    });
  });
}

// Function to show login modal
function showLoginModal() {
  // Vérifier si la modal existe déjà
  let existingModal = document.getElementById('login-modal');
  if (existingModal) {
    existingModal.style.display = 'flex';
    return;
  }
  
  // Créer la modal
  const modal = document.createElement('div');
  modal.id = 'login-modal';
  modal.style.cssText = `
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    backdrop-filter: blur(3px);
  `;
  
  // Contenu de la modal
  const modalContent = document.createElement('div');
  modalContent.style.cssText = `
    background: #fff;
    padding: 32px 28px;
    border-radius: 12px;
    max-width: 420px;
    width: 90%;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    position: relative;
    text-align: center;
    animation: modalSlideIn 0.3s ease-out;
  `;
  
  // Ajouter l'animation CSS
  const style = document.createElement('style');
  style.textContent = `
    @keyframes modalSlideIn {
      from {
        opacity: 0;
        transform: translateY(-30px) scale(0.95);
      }
      to {
        opacity: 1;
        transform: translateY(0) scale(1);
      }
    }
  `;
  document.head.appendChild(style);
  
  modalContent.innerHTML = `
    <button id="close-modal" style="
      position: absolute;
      top: 12px;
      right: 16px;
      background: none;
      border: none;
      font-size: 24px;
      color: #999;
      cursor: pointer;
      padding: 4px;
      line-height: 1;
      transition: color 0.2s;
    " onmouseover="this.style.color='#333'" onmouseout="this.style.color='#999'">&times;</button>
    
    <div style="margin-bottom: 24px;">
      <i class="fa-solid fa-heart" style="
        font-size: 48px;
        color: #f1bb69;
        margin-bottom: 16px;
        display: block;
      "></i>
      <h3 style="
        color: #0E2141;
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 12px;
        line-height: 1.2;
      ">Connexion</h3>
      <p style="
        color: #666;
        font-size: 16px;
        line-height: 1.5;
        margin-bottom: 24px;
      ">Connectez-vous pour ajouter ce produit à vos favoris.</p>
    </div>
    
    <form id="login-form" style="text-align: left;">
      <div style="margin-bottom: 16px;">
        <label style="
          display: block;
          color: #0E2141;
          font-weight: 600;
          margin-bottom: 6px;
          font-size: 14px;
        ">Email ou nom d'utilisateur</label>
        <input type="text" id="login-username" required style="
          width: 100%;
          padding: 12px 16px;
          border: 2px solid #e3e5e8;
          border-radius: 8px;
          font-size: 16px;
          box-sizing: border-box;
          transition: border-color 0.2s;
        " onfocus="this.style.borderColor='#f1bb69'" onblur="this.style.borderColor='#e3e5e8'">
      </div>
      
      <div style="margin-bottom: 20px;">
        <label style="
          display: block;
          color: #0E2141;
          font-weight: 600;
          margin-bottom: 6px;
          font-size: 14px;
        ">Mot de passe</label>
        <input type="password" id="login-password" required style="
          width: 100%;
          padding: 12px 16px;
          border: 2px solid #e3e5e8;
          border-radius: 8px;
          font-size: 16px;
          box-sizing: border-box;
          transition: border-color 0.2s;
        " onfocus="this.style.borderColor='#f1bb69'" onblur="this.style.borderColor='#e3e5e8'">
      </div>
      
      <div id="login-error" style="
        display: none;
        background: #ffe6e6;
        color: #d63031;
        padding: 12px;
        border-radius: 6px;
        margin-bottom: 16px;
        font-size: 14px;
        border: 1px solid #fab1a0;
      "></div>
      
      <div style="display: flex; gap: 12px; justify-content: center;">
        <button type="submit" id="submit-login" style="
          background: #f1bb69;
          color: #0E2141;
          border: none;
          padding: 14px 24px;
          border-radius: 8px;
          font-size: 16px;
          font-weight: 600;
          cursor: pointer;
          transition: all 0.2s;
          min-width: 140px;
        " onmouseover="this.style.background='#e29a3d'" onmouseout="this.style.background='#f1bb69'">
          Se connecter
        </button>
        
        <button type="button" id="cancel-login" style="
          background: #f8f9fa;
          color: #6c757d;
          border: 1px solid #dee2e6;
          padding: 14px 24px;
          border-radius: 8px;
          font-size: 16px;
          font-weight: 500;
          cursor: pointer;
          transition: all 0.2s;
          min-width: 120px;
        " onmouseover="this.style.background='#e9ecef'; this.style.color='#495057'" onmouseout="this.style.background='#f8f9fa'; this.style.color='#6c757d'">
          Annuler
        </button>
      </div>
    </form>
  `;
  
  modal.appendChild(modalContent);
  document.body.appendChild(modal);
  
  // Événements de fermeture
  function closeModal() {
    modal.style.display = 'none';
  }
  
  // Fermer avec le bouton X
  document.getElementById('close-modal').addEventListener('click', closeModal);
  
  // Fermer en cliquant sur le background
  modal.addEventListener('click', function(e) {
    if (e.target === modal) {
      closeModal();
    }
  });
  
  // Bouton annuler
  document.getElementById('cancel-login').addEventListener('click', closeModal);
  
  // Gérer le formulaire de connexion
  document.getElementById('login-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const username = document.getElementById('login-username').value;
    const password = document.getElementById('login-password').value;
    const submitBtn = document.getElementById('submit-login');
    const errorDiv = document.getElementById('login-error');
    
    // Validation côté client
    if (!username || !password) {
      showLoginError('Veuillez remplir tous les champs.');
      return;
    }
    
    // Désactiver le bouton et afficher un loader
    submitBtn.disabled = true;
    submitBtn.textContent = 'Connexion...';
    submitBtn.style.opacity = '0.7';
    errorDiv.style.display = 'none';
    
    // Créer les données pour la requête AJAX
    const formData = new FormData();
    formData.append('action', 'ajax_login');
    formData.append('username', username);
    formData.append('password', password);
    formData.append('security', wishlist_ajax.nonce);
    
    // Envoyer la requête AJAX
    fetch(wishlist_ajax.ajax_url, {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        // Connexion réussie
        submitBtn.textContent = 'Connecté !';
        submitBtn.style.background = '#27ae60';
        
        // Recharger la page après un délai
        setTimeout(() => {
          window.location.reload();
        }, 1000);
      } else {
        // Erreur de connexion
        showLoginError(data.data ? data.data.message : 'Identifiants incorrects.');
        resetLoginButton();
      }
    })
    .catch(error => {
      console.error('Erreur AJAX:', error);
      showLoginError('Erreur de connexion. Veuillez réessayer.');
      resetLoginButton();
    });
    
    function showLoginError(message) {
      errorDiv.textContent = message;
      errorDiv.style.display = 'block';
    }
    
    function resetLoginButton() {
      submitBtn.disabled = false;
      submitBtn.textContent = 'Se connecter';
      submitBtn.style.opacity = '1';
      submitBtn.style.background = '#f1bb69';
    }
  });
  
  // Fermer avec la touche Escape
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && modal.style.display === 'flex') {
      closeModal();
    }
  });
}

const COLOR_MAP = {
  'rouge': '#e74c3c',
  'jaune': '#f1c40f',
  'bleu': '#2980d9',
  'bleu clair': '#5dade2',
  'vert': '#27ae60',
  'orange': '#f39c12',
  'noir': '#222',
  'noire': '#222',
  'blanc': '#fff',
  'gris': '#bdc3c7',
  'violet': '#8e44ad',
  'rose': '#fd79a8',
  'marron': '#8d5524',
  'beige': '#f5e6ca',
  'transparent': 'transparent',
};
