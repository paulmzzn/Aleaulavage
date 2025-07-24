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
          });
          swatchContainer.appendChild(swatch);
        });
        // Insert swatches after the label, on a new line
        const br = document.createElement('br');
        label.parentNode.insertBefore(br, label.nextSibling);
        label.parentNode.insertBefore(swatchContainer, br.nextSibling);
        // Add/align Effacer button
        let clearBtn = form.querySelector('.reset_variations');
        if (clearBtn) {
          clearBtn.classList.add('swatch-clear-btn');
          swatchContainer.appendChild(clearBtn);
          clearBtn.addEventListener('click', function(e) {
            e.preventDefault();
            // Deselect all swatches
            swatchContainer.querySelectorAll('.color-swatch').forEach(btn => btn.classList.remove('selected'));
            // Reset select
            select.value = '';
            select.dispatchEvent(new Event('change', {bubbles:true}));
            // Hide color badge
            var badge = document.getElementById('selected-color-badge');
            if (badge) badge.style.display = 'none';
            // Trigger WooCommerce reset event for full UI reset
            var form = select.closest('form');
            if (form) {
              var event = document.createEvent('Event');
              event.initEvent('reset_variations', true, true);
              form.dispatchEvent(event);
            }
            // Forcer le header prix à l'échelle par défaut, sans dépendre de WooCommerce
            setTimeout(function() {
              var priceHeader = document.querySelector('.purchase-header .price');
              if (priceHeader && defaultPrice) {
                priceHeader.innerHTML = defaultPrice;
              }
              // 2. Simuler un changement sur tous les selects pour forcer WooCommerce à masquer la variation
              var selects = form ? form.querySelectorAll('select') : [];
              selects.forEach(function(sel) {
                sel.dispatchEvent(new Event('change', {bubbles:true}));
              });
              // 3. Masquer et vider explicitement le bloc variation WooCommerce (anti-fantôme)
              var singleVar = document.querySelector('.single_variation');
              if (singleVar) {
                singleVar.style.display = 'none';
                singleVar.innerHTML = '';
              }
            }, 50);
          });
        }
      }
    });
  });
  setupSwatchBadgeSync();
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
