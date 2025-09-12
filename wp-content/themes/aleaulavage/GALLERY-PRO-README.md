# 🖼️ Système de Galerie d'Images Professionnel - Aleaulavage

## 📋 Présentation

Nouveau système de galerie d'images haute performance pour votre site e-commerce, remplaçant l'ancien système bugué par une solution moderne et professionnelle.

## ✨ Fonctionnalités

### 🔍 Zoom Multi-niveaux
- **Zoom au survol** (desktop) : 2x instantané et fluide
- **Zoom par clic** : 3 niveaux (1x, 1.5x, 2x, 2.5x, 3x)
- **Point de zoom précis** : Zoom centré sur le clic
- **Indicateur visuel** : Affichage du niveau de zoom actuel

### 📱 Support Mobile Complet
- **Navigation tactile** : Swipe gauche/droite entre images
- **Zoom optimisé** : Clic pour ouvrir en lightbox sur mobile
- **Interface adaptative** : Contrôles tactiles optimisés
- **Performance** : Pas de zoom hover sur mobile pour éviter les conflits

### 🎯 Navigation Avancée
- **Flèches clavier** : ← → pour naviguer entre images
- **Boutons visuels** : Contrôles élégants avec animations
- **Thumbnails interactifs** : Prévisualisation et navigation rapide
- **Auto-scroll** : Les thumbnails suivent l'image active

### 🌟 Lightbox Professionnel
- **Plein écran** : Visualisation optimale des images
- **Navigation** : Flèches et clavier dans le lightbox
- **Compteur** : Position actuelle / total des images
- **Fermeture** : Échap, clic backdrop, ou bouton fermer

### ⚡ Performances
- **Lazy loading** : Chargement intelligent des images
- **Préchargement** : Images suivantes préchargées en arrière-plan
- **Optimisation responsive** : Adaptation automatique à tous écrans
- **Cache intelligent** : Évite les rechargements inutiles

## 🚀 Installation

### 1. Fichiers Installés
```
wp-content/themes/aleaulavage/
├── js/pro-product-gallery.js       # Script principal
├── css/pro-product-gallery.css     # Styles modernes
├── js/gallery-installer.js         # Assistant d'installation
└── GALLERY-PRO-README.md          # Ce guide
```

### 2. Intégration Automatique
Le système s'active automatiquement sur les pages produits :
- ✅ Scripts et styles chargés automatiquement
- ✅ Ancien système désactivé proprement
- ✅ Compatibilité WooCommerce maintenue
- ✅ Support des variations produits

### 3. Vérification
Pour vérifier l'installation :
1. Aller sur une page produit avec plusieurs images
2. Vérifier le zoom au survol (desktop)
3. Tester la navigation par thumbnails
4. Essayer le mode plein écran

## 🎨 Personnalisation

### Variables CSS
Modifiez les couleurs dans `/css/pro-product-gallery.css` :

```css
:root {
    --gallery-primary: #f1bb69;     /* Couleur principale */
    --gallery-dark: #0E2141;        /* Texte foncé */
    --gallery-light: #fafbfc;       /* Arrière-plans clairs */
    --gallery-border: #e3e5e8;      /* Bordures */
    --gallery-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); /* Ombres */
    --gallery-radius: 12px;         /* Arrondi des coins */
}
```

### Tailles d'Images
Nouvelles tailles optimisées générées automatiquement :
- `product-gallery-main` : 800x800px (image principale)
- `product-gallery-thumb` : 150x150px (thumbnails)
- `product-gallery-full` : 1200x1200px (lightbox)

## 🔧 Configuration Avancée

### API JavaScript
```javascript
// Accéder à l'instance de la galerie
const gallery = window.proGallery;

// Méthodes disponibles
gallery.goToImage(2);        // Aller à l'image index 2
gallery.nextImage();         // Image suivante
gallery.previousImage();     // Image précédente  
gallery.openLightbox();      // Ouvrir le lightbox
gallery.closeLightbox();     // Fermer le lightbox
gallery.refresh();           // Recharger la galerie
```

### Events Personnalisés
```javascript
// Écouter les changements d'image
document.addEventListener('gallery-image-changed', function(e) {
    console.log('Nouvelle image:', e.detail.index);
});

// Réinitialiser après variation produit
document.addEventListener('woocommerce_variation_has_changed', function() {
    if (window.proGallery) {
        window.proGallery.refresh();
    }
});
```

## 📱 Responsive Design

### Desktop (1024px+)
- Zoom au survol activé
- Tous les contrôles visibles au hover
- Navigation clavier complète
- Lightbox avec raccourcis clavier

### Tablet (768px - 1024px)
- Contrôles tactiles optimisés
- Thumbnails adaptés (70x70px)
- Navigation par swipe

### Mobile (< 768px)
- Interface simplifiée et tactile
- Contrôles toujours visibles
- Clic = lightbox direct
- Thumbnails compacts (60x60px)

## 🐛 Résolution des Problèmes

### Images ne se chargent pas
1. Vérifier que les images existent dans la galerie WooCommerce
2. Régénérer les miniatures : **Outils → Regen. Thumbnails**
3. Vider le cache si utilisé

### Zoom ne fonctionne pas
1. Vérifier que JavaScript est activé
2. Ouvrir la console développeur (F12) pour voir les erreurs
3. S'assurer qu'il n'y a pas de conflit avec d'autres plugins

### Conflit avec d'autres plugins
1. Le système désactive automatiquement l'ancien flexslider
2. En cas de conflit, désactiver temporairement les plugins de galerie
3. Vérifier la console pour les erreurs JavaScript

## 🔄 Migration depuis l'ancien système

### Automatique
- ✅ Ancien système désactivé automatiquement
- ✅ Fichiers sauvegardés (.backup)
- ✅ Styles WooCommerce préservés
- ✅ Fonctionnalités améliorées

### Manuel (si nécessaire)
1. Supprimer les références à `single-product-zoom.js`
2. Supprimer les styles `zoom-enhancements.css`
3. Vider les caches (plugin + navigateur)

## 📊 Performance

### Optimisations Intégrées
- **Lazy loading** automatique
- **Préchargement intelligent** des images
- **CSS optimisé** avec variables et animations fluides
- **JavaScript moderne** avec gestion mémoire
- **Cache navigateur** maximisé
- **Images responsive** selon l'écran

### Métriques Cibles
- **LCP** : < 2.5s (Largest Contentful Paint)
- **FID** : < 100ms (First Input Delay)  
- **CLS** : < 0.1 (Cumulative Layout Shift)

## 🎯 Prochaines Améliorations

### Version 1.1 (Prévue)
- [ ] Mode comparaison d'images
- [ ] Zoom 360° pour certains produits
- [ ] Vidéos dans la galerie
- [ ] Partage social des images

### Version 1.2 (Future)
- [ ] Intelligence artificielle pour l'optimisation
- [ ] Mode réalité augmentée
- [ ] Intégration avec les avis clients

---

## 📞 Support

En cas de problème :
1. Vérifier ce README
2. Consulter la console navigateur (F12)
3. Tester sur différents navigateurs
4. Vérifier les conflits plugins

**Système créé avec ❤️ pour Aleaulavage**