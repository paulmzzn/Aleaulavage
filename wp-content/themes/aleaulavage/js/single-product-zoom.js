// Zoom pour galerie produit WooCommerce - fonctionne sur toutes les images
(function() {
  'use strict';
  
  var currentImageSrc = '';
  var currentZoomData = null;
  
  function applyZoomToCurrentImage() {
    
    // Trouver l'image principale visible (celle qui est affichée actuellement)
    var imageContainer = document.querySelector('.product-gallery .woocommerce-product-gallery__image.flex-active-slide') || 
                        document.querySelector('.product-gallery .woocommerce-product-gallery__image:first-child');
    if (!imageContainer) {
      return;
    }
    
    // Vérifier que le container est visible
    var containerRect = imageContainer.getBoundingClientRect();
    if (containerRect.width === 0 || containerRect.height === 0) {
      return;
    }
    
    var image = imageContainer.querySelector('img');
    if (!image) {
      return;
    }
    
    // Vérifier rapidement si l'image est prête
    if (!image.complete || image.naturalWidth === 0) {
      // Attendre le chargement mais avec timeout pour éviter les blocages
      var loadHandler = function() {
        image.removeEventListener('load', loadHandler);
        applyZoomToCurrentImage();
      };
      image.addEventListener('load', loadHandler);
      // Timeout de sécurité - essayer quand même après 500ms
      setTimeout(function() {
        if (!image.complete) {
          image.removeEventListener('load', loadHandler);
          applyZoomToCurrentImage();
        }
      }, 500);
      return;
    }
    
    // Vérifier si l'image a changé (mais ne pas bloquer si les listeners manquent)
    if (image.src === currentImageSrc && currentZoomData && currentZoomData.container === imageContainer) {
      return;
    }
    
    currentImageSrc = image.src;
    
    // Supprimer les anciens listeners s'ils existent
    if (currentZoomData) {
      var oldContainer = currentZoomData.container;
      if (oldContainer) {
        oldContainer.removeEventListener('mouseenter', currentZoomData.mouseEnter);
        oldContainer.removeEventListener('mousemove', currentZoomData.mouseMove);
        oldContainer.removeEventListener('mouseleave', currentZoomData.mouseLeave);
        oldContainer.removeEventListener('click', currentZoomData.click);
      }
    }
    
    // Variables pour le zoom
    var clickZoomLevel = 0;
    var zoomLevels = [1, 2, 3];
    var isMouseOver = false; // Track si la souris est sur l'image
    
    // Fonctions de zoom
    function onMouseEnter() {
      if (clickZoomLevel > 0) return;
      isMouseOver = true;
      image.style.cursor = 'zoom-in';
    }
    
    function onMouseMove(e) {
      if (clickZoomLevel > 0) return; // Pas de zoom souris si zoom clic actif
      if (window.innerWidth < 768) return; // Pas sur mobile
      if (!isMouseOver) return; // S'assurer que la souris est bien sur l'image
      
      var rect = image.getBoundingClientRect();
      var x = (e.clientX - rect.left) / rect.width;
      var y = (e.clientY - rect.top) / rect.height;
      
      // Vérifier que les coordonnées sont valides
      if (x < 0 || x > 1 || y < 0 || y > 1) return;
      
      // Clamp
      x = Math.max(0, Math.min(1, x));
      y = Math.max(0, Math.min(1, y));
      
      // Désactiver les transitions pour l'instantanéité
      image.classList.add('is-zooming');
      
      // Zoom fixe et modéré pour éviter les problèmes
      var scale = 1.5;
      image.style.transformOrigin = (x * 100) + '% ' + (y * 100) + '%';
      image.style.transform = 'scale(' + scale + ')';
    }
    
    function onMouseLeave() {
      if (clickZoomLevel > 0) return;
      
      isMouseOver = false;
      // Réactiver les transitions pour la sortie
      image.classList.remove('is-zooming');
      image.style.transform = '';
      image.style.transformOrigin = '';
      image.style.cursor = 'zoom-in';
    }
    
    function onClick(e) {
      e.preventDefault();
      
      // Cycle entre les niveaux
      clickZoomLevel = (clickZoomLevel + 1) % 3;
      var baseScale = zoomLevels[clickZoomLevel];
      
      
      if (baseScale === 1) {
        // Retour normal
        image.style.transform = '';
        image.style.transformOrigin = '';
        image.style.cursor = 'zoom-in';
        imageContainer.classList.remove('click-zoomed');
      } else {
        // Zoom sur point de clic avec adaptation à la taille
        var rect = image.getBoundingClientRect();
        var containerRect = imageContainer.getBoundingClientRect();
        var x = (e.clientX - rect.left) / rect.width;
        var y = (e.clientY - rect.top) / rect.height;
        
        x = Math.max(0, Math.min(1, x));
        y = Math.max(0, Math.min(1, y));
        
        image.style.transformOrigin = (x * 100) + '% ' + (y * 100) + '%';
        image.style.transform = 'scale(' + baseScale + ')';
        image.style.cursor = clickZoomLevel === 2 ? 'zoom-out' : 'zoom-in';
        imageContainer.classList.add('click-zoomed');
      }
    }
    
    // Ajouter les event listeners
    imageContainer.addEventListener('mouseenter', onMouseEnter);
    imageContainer.addEventListener('mousemove', onMouseMove);
    imageContainer.addEventListener('mouseleave', onMouseLeave);
    imageContainer.addEventListener('click', onClick);
    
    // Sauvegarder les données pour pouvoir les supprimer plus tard
    currentZoomData = {
      container: imageContainer,
      mouseEnter: onMouseEnter,
      mouseMove: onMouseMove,
      mouseLeave: onMouseLeave,
      click: onClick
    };
    
  }
  
  function setupThumbnailWatcher() {
    
    // Chercher les thumbnails et les images de la galerie
    var thumbnails = document.querySelectorAll('.product-gallery .flex-control-thumbs li, .product-gallery .woocommerce-product-gallery__image');
    
    thumbnails.forEach(function(thumbnail) {
      thumbnail.addEventListener('click', function() {
        // Réappliquer le zoom rapidement après changement
        setTimeout(function() {
          applyZoomToCurrentImage();
        }, 100);
      });
    });
    
    // Surveiller les changements de classe flex-active-slide
    var observer = new MutationObserver(function(mutations) {
      mutations.forEach(function(mutation) {
        if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
          var target = mutation.target;
          if (target.classList.contains('flex-active-slide')) {
            setTimeout(applyZoomToCurrentImage, 100);
          }
        }
      });
    });
    
    // Observer tous les containers d'images
    var imageContainers = document.querySelectorAll('.product-gallery .woocommerce-product-gallery__image');
    imageContainers.forEach(function(container) {
      observer.observe(container, { attributes: true, attributeFilter: ['class'] });
    });
    
  }
  
  function initializeZoom() {
    
    // Appliquer le zoom sur l'image actuelle
    applyZoomToCurrentImage();
    
    // Configurer la surveillance des thumbnails
    setupThumbnailWatcher();
    
    // Vérification périodique moins agressive mais plus efficace
    setInterval(function() {
      var currentImage = document.querySelector('.product-gallery .woocommerce-product-gallery__image.flex-active-slide img') ||
                       document.querySelector('.product-gallery .woocommerce-product-gallery__image:first-child img');
      if (currentImage && currentImage.src !== currentImageSrc && currentImage.complete) {
        applyZoomToCurrentImage();
      }
    }, 300); // Vérification plus rapide
  }

  // Initialisation immédiate et optimisée
  function startZoomWhenReady() {
    // Essayer immédiatement
    if (document.querySelector('.product-gallery .woocommerce-product-gallery__image')) {
      initializeZoom();
    } else {
      // Si pas prêt, attendre un peu et réessayer
      setTimeout(function() {
        if (document.querySelector('.product-gallery .woocommerce-product-gallery__image')) {
          initializeZoom();
        } else {
          // Dernier recours avec délai plus long
          setTimeout(initializeZoom, 800);
        }
      }, 200);
    }
  }
  
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', startZoomWhenReady);
  } else {
    startZoomWhenReady();
  }
  
})();