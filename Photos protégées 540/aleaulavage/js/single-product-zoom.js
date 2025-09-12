// Effet de zoom qui suit la souris sur l'image produit WooCommerce
(function() {
  document.addEventListener('DOMContentLoaded', function() {
    var gallery = document.querySelector('.product-gallery .woocommerce-product-gallery__image');
    if (!gallery) return;
    var img = gallery.querySelector('img');
    if (!img) return;
    gallery.classList.add('zoom-follow');

    var scale = 1.6; // Facteur de zoom
    var isZooming = false;

    function onMouseMove(e) {
      var rect = gallery.getBoundingClientRect();
      var imgRect = img.getBoundingClientRect();
      // Calculer la position relative à l'image (pas au conteneur)
      var x = (e.clientX - imgRect.left) / imgRect.width;
      var y = (e.clientY - imgRect.top) / imgRect.height;
      // Clamp entre 0 et 1
      x = Math.max(0, Math.min(1, x));
      y = Math.max(0, Math.min(1, y));
      // Calcul du déplacement pour centrer le zoom sous la souris
      var tx = ((x - 0.5) * (imgRect.width * (scale - 1)));
      var ty = ((y - 0.5) * (imgRect.height * (scale - 1)));
      img.style.transform = 'scale(' + scale + ') translate(' + (-tx/scale) + 'px,' + (-ty/scale) + 'px)';
      img.classList.add('is-zooming');
      gallery.classList.add('zooming');
      isZooming = true;
    }
    function onMouseLeave() {
      img.style.transform = '';
      img.classList.remove('is-zooming');
      gallery.classList.remove('zooming');
      isZooming = false;
    }
    gallery.addEventListener('mousemove', onMouseMove);
    gallery.addEventListener('mouseleave', onMouseLeave);
  });
})();
