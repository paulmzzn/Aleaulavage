document.addEventListener('DOMContentLoaded', function() {
  var priceHeader = document.querySelector('.purchase-header .price');
  var variationForm = document.querySelector('.variations_form');
  if (!priceHeader || !variationForm) return;

  var defaultPrice = priceHeader.innerHTML;

  function updatePriceFromVariation() {
    var variationPrice = document.querySelector('.single_variation .woocommerce-variation-price .price');
    if (variationPrice && variationPrice.textContent.trim() !== '') {
      priceHeader.innerHTML = variationPrice.outerHTML;
    } else {
      priceHeader.innerHTML = defaultPrice;
    }
  }
  window.updatePriceFromVariation = updatePriceFromVariation;

  // Listen to WooCommerce variation events
  variationForm.addEventListener('show_variation', updatePriceFromVariation);
  variationForm.addEventListener('hide_variation', updatePriceFromVariation);

  // Listen to all select changes for instant feedback
  variationForm.querySelectorAll('select').forEach(function(select) {
    select.addEventListener('change', function() {
      setTimeout(updatePriceFromVariation, 0);
    });
    select.addEventListener('input', function() {
      setTimeout(updatePriceFromVariation, 0);
    });
  });

  // MutationObserver to catch any DOM change in .single_variation
  var singleVariation = document.querySelector('.single_variation');
  if (singleVariation) {
    var observer = new MutationObserver(updatePriceFromVariation);
    observer.observe(singleVariation, { childList: true, subtree: true });
  }

  // On page load, in case a variation is preselected
  updatePriceFromVariation();
});
