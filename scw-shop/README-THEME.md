# SCW Shop - Thème WordPress

Thème WordPress professionnel et maintenable pour SCW Shop, adapté depuis React.

## Structure du thème

```
scw-shop/
├── assets/
│   ├── css/
│   │   ├── header.css          # Styles du header
│   │   ├── footer.css          # Styles du footer
│   │   ├── components.css      # Composants réutilisables
│   │   └── admin.css           # Styles admin WordPress
│   ├── js/
│   │   ├── main.js             # Scripts globaux
│   │   └── header.js           # Interactions du header
│   └── images/                 # Images du thème
│
├── inc/
│   ├── theme-setup.php         # Configuration du thème
│   ├── enqueue-scripts.php     # Chargement CSS/JS
│   ├── template-functions.php  # Fonctions de templates
│   ├── user-roles.php          # Gestion des rôles (guest, reseller, client)
│   └── woocommerce.php         # Compatibilité WooCommerce
│
├── template-parts/
│   ├── header/
│   │   └── header-main.php     # Template du header
│   ├── footer/
│   │   └── footer-main.php     # Template du footer
│   ├── content/                # Templates de contenu
│   └── components/             # Composants réutilisables
│       ├── product-card.php
│       ├── product-slider.php
│       └── promo-carousel.php
│
├── templates/                  # Templates de pages
│   ├── page-shop.php
│   ├── page-profile.php
│   ├── single-product.php
│   └── page-checkout.php
│
├── woocommerce/                # Templates WooCommerce personnalisés
│
├── style.css                   # Feuille de style principale
├── functions.php               # Point d'entrée du thème
├── header.php                  # En-tête HTML
├── footer.php                  # Pied de page HTML
└── index.php                   # Template principal
```

## Fonctionnalités implémentées

### ✅ Header adapté depuis React

Le header a été complètement adapté depuis le composant React vers WordPress :

#### Fonctionnalités :
- **Sticky header** avec backdrop-filter
- **Navigation desktop** avec dropdown catégories au survol
- **Menu mobile** avec sidebar et overlay
- **Barre de recherche** responsive
- **Actions utilisateur** :
  - Bouton "Se connecter" pour les invités
  - Icônes compte, favoris, panier pour les utilisateurs connectés
  - Compteur de panier dynamique
- **Styles conditionnels** selon le rôle utilisateur (guest, reseller, client)

#### Fichiers créés :
- `template-parts/header/header-main.php` - Template PHP
- `assets/css/header.css` - Styles
- `assets/js/header.js` - Interactions JavaScript

### 🎨 Système de couleurs dynamique

Variables CSS configurées selon le rôle utilisateur :
- `--color-primary-dark: #0f172a` - Couleur primaire foncée
- `--color-primary-light: #1e293b` - Couleur primaire claire
- `--color-accent-default: #0ea5e9` - Accent par défaut
- `--color-reseller-accent: #4338ca` - Accent revendeur
- `--site-accent-color` - Dynamique selon le rôle
- `--user-store-color` - Couleur personnalisée du revendeur

### 👥 Gestion des rôles utilisateur

3 rôles distincts :
1. **Guest** - Visiteur non connecté (prix floutés, pas d'achat)
2. **Reseller** - Revendeur avec 3 modes :
   - Gestion - Édition des produits et prix
   - Achat - Mode achat
   - Vitrine - Affichage pour clients
3. **Client** - Client final du revendeur

Fonctions disponibles :
- `scw_shop_get_user_role()` - Obtenir le rôle actuel
- `scw_shop_get_user_mode()` - Obtenir le mode (pour resellers)
- `scw_shop_set_user_mode($mode)` - Changer de mode
- `scw_shop_get_user_store_color()` - Couleur personnalisée
- `scw_shop_can_see_prices()` - Vérifier si peut voir les prix

### 🛒 Intégration WooCommerce

- Support complet WooCommerce
- Galerie produit (zoom, lightbox, slider)
- Affichage conditionnel selon le rôle :
  - Prix masqués pour les invités
  - Bouton "Se connecter" au lieu d'"Ajouter au panier"
- 3 colonnes de produits par défaut
- 12 produits par page

## Bonnes pratiques implémentées

### 📁 Architecture modulaire
- Séparation logique en fichiers inc/
- Template parts réutilisables
- Assets organisés par type

### 🔒 Sécurité
- Escape de toutes les sorties (`esc_html`, `esc_url`, `esc_attr`)
- Vérification des capacités utilisateur
- Nonces pour AJAX
- Validation et sanitization

### ♿ Accessibilité
- Attributs `aria-label` sur les boutons
- Navigation au clavier
- Support écran lecteur

### 📱 Responsive
- Mobile-first approach
- Breakpoints :
  - < 768px : Mobile
  - 768px - 1024px : Tablet
  - > 1024px : Desktop
- Menu mobile avec sidebar

### 🚀 Performance
- CSS et JS minifiés en production
- Chargement conditionnel des scripts
- Images lazy-loading (via WordPress)
- Cache WooCommerce fragments

### 🌐 Internationalisation
- Toutes les chaînes traduisibles
- Text domain : `scw-shop`
- Fonctions `__()` et `_e()` utilisées

## Prochaines étapes

Pour continuer l'adaptation React → WordPress :

1. **Footer** - Adapter le composant Footer.jsx
2. **ProductCard** - Créer template-parts/components/product-card.php
3. **ProductSlider** - Adapter le carousel de produits
4. **PromoCarousel** - Adapter le carousel promotionnel
5. **Pages** :
   - Shop.jsx → templates/page-shop.php
   - Profile.jsx → templates/page-profile.php
   - ProductDetail.jsx → single-product.php
   - Cart.jsx → woocommerce/cart/cart.php
   - Checkout.jsx → woocommerce/checkout/form-checkout.php

## Utilisation

### Activer le thème

1. Démarrer WordPress avec Docker :
   ```bash
   cd wp-scw
   docker-compose up -d
   ```

2. Accéder à l'admin WordPress :
   - URL : http://localhost:8080/wp-admin
   - Activer le thème SCW Shop

### Développement

Les fichiers du thème sont synchronisés en temps réel :
- Modifier `wp-scw/themes/scw-shop/*`
- Les changements sont instantanément visibles dans WordPress

### Ajouter des catégories de produits

Les catégories s'affichent automatiquement dans le menu déroulant.
Pour les créer :
- Produits > Catégories dans l'admin WordPress

### Configuration des rôles

Les rôles sont automatiquement créés lors de l'activation du thème :
- Utilisateurs > Ajouter
- Choisir le rôle "Revendeur" ou "Client"

## Support

Pour toute question sur le thème :
- Consulter le code commenté
- Vérifier les fichiers dans `inc/`
- Utiliser les fonctions helper définies dans `template-functions.php`

## Auteur

SCW - 2025
