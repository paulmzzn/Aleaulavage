// Met à jour dynamiquement le prix affiché selon la quantité et le tableau ADP
// Place ce fichier dans le thème et inclus-le côté produit

document.addEventListener('DOMContentLoaded', function() {
  var qtyInput = document.querySelector('.purchase-form input[name="quantity"]');
  var priceEl = document.querySelector('.purchase-header .price');
  var adpTable = document.querySelector('.adp-bulk-table-wrapper table');
  if (!qtyInput || !priceEl || !adpTable) return;

  // Récupère le prix de base (float)
  var basePrice = parseFloat(
    priceEl.textContent.replace(/[^\d,.]/g, '').replace(',', '.')
  );
  if (isNaN(basePrice)) return;

  // Parse le tableau ADP pour obtenir les règles
  function getAdpRules() {
    var rules = [];
    var rows = adpTable.querySelectorAll('tbody tr');
    rows.forEach(function(row) {
      var cells = row.querySelectorAll('td');
      if (cells.length >= 2) {
        var min = 1, max = 999999, prix = null;
        var qte = cells[0].textContent.trim();
        var prixTxt = cells[1].textContent.trim().replace(/[^\d,.]/g, '').replace(',', '.');
        prix = parseFloat(prixTxt);
        if (qte.includes('+')) {
          min = parseInt(qte);
          max = 999999;
        } else if (qte.includes('-')) {
          var parts = qte.split('-');
          min = parseInt(parts[0]);
          max = parseInt(parts[1]);
        } else {
          min = max = parseInt(qte);
        }
        if (!isNaN(min) && !isNaN(max) && !isNaN(prix)) {
          rules.push({min, max, prix});
        }
      }
    });
    return rules;
  }

  var adpRules = getAdpRules();
  var defaultHtml = priceEl.innerHTML;

  function updatePrice() {
    var qty = parseInt(qtyInput.value) || 1;
    var found = adpRules.find(function(rule) {
      return qty >= rule.min && qty <= rule.max;
    });
    if (found && found.prix < basePrice) {
      priceEl.innerHTML = '<del style="color:#999;text-decoration:line-through;">' +
        basePrice.toFixed(2).replace('.', ',') + ' €</del> <span style="color:#5899E2;font-weight:bold;">' +
        found.prix.toFixed(2).replace('.', ',') + ' €</span>';
    } else {
      priceEl.innerHTML = defaultHtml;
    }
  }

  qtyInput.addEventListener('input', updatePrice);
  qtyInput.addEventListener('change', updatePrice);
  updatePrice();
});
