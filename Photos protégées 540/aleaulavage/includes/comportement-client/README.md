# Système de Comportement Client - Version 2.0

## 📋 Vue d'ensemble

Le système de comportement client a été entièrement restructuré pour suivre les bonnes pratiques de développement WordPress. Cette nouvelle version modulaire inclut la détection automatique du type d'appareil et une interface d'administration améliorée.

## 🆕 Nouvelles fonctionnalités

### Détection automatique d'appareil
- **📱 Mobile** : Smartphones et appareils mobiles
- **💻 PC** : Ordinateurs de bureau et portables  
- **📱 Tablette** : iPads et tablettes Android
- **❓ Inconnu** : Appareils non détectés

### Interface améliorée
- Dashboard avec statistiques par device
- Graphiques de répartition des appareils
- Export CSV avec informations de device
- Interface responsive

## 📁 Architecture modulaire

```
comportement-client/
├── init.php                 # Initialisation principale
├── database.php            # Gestion de la base de données
├── session.php             # Sessions et détection device
├── panier-tracker.php      # Tracking des paniers
├── recherche-tracker.php   # Tracking des recherches
├── admin.php              # Interface d'administration
├── admin.css              # Styles d'administration
├── admin.js               # Scripts d'administration
└── README.md              # Cette documentation
```

## 🔧 Installation et utilisation

### Remplacement de l'ancien système

1. **Sauvegarde** : Sauvegarder l'ancien fichier `admin-comportement.php`
2. **Remplacement** : Remplacer l'inclusion par :
   ```php
   require_once get_template_directory() . '/includes/admin-comportement-nouveau.php';
   ```
3. **Migration** : Les données existantes sont automatiquement migrées

### Compatibilité

Le nouveau système maintient la compatibilité avec l'ancien :
- `obtenir_session_id()` : Fonction conservée
- `determiner_statut_panier()` : Fonction conservée
- Tables de base de données : Mises à jour automatiquement

## 📊 Base de données

### Nouvelles colonnes ajoutées

**Table `paniers_anonymes`** :
- `device_type` (VARCHAR(20)) : Type d'appareil
- Index sur `device_type` pour les performances

**Table `recherches_anonymes`** :
- `device_type` (VARCHAR(20)) : Type d'appareil  
- Index sur `device_type` pour les performances

## 🔍 Classes principales

### `ComportementClientSession`
- Gestion des sessions utilisateur
- Détection automatique du type d'appareil
- Gestion des cookies de session

### `ComportementClientPanierTracker`
- Tracking des ajouts au panier
- Gestion des utilisateurs connectés/anonymes
- Transfert panier anonyme → connecté

### `ComportementClientRechercheTracker`
- Tracking des recherches
- Statistiques par device
- Historique des recherches utilisateur

### `ComportementClientAdmin`
- Interface d'administration
- Exports CSV avec données device
- Dashboard avec statistiques

### `ComportementClientDatabase`
- Création et gestion des tables
- Migrations automatiques
- Nettoyage des anciennes données

## 🎯 Bonnes pratiques implémentées

1. **Architecture modulaire** : Séparation des responsabilités
2. **Namespacing** : Classes avec préfixes uniques
3. **Hooks WordPress** : Utilisation appropriée des actions/filtres
4. **Sécurité** : Validation et sanitization des données
5. **Performance** : Index de base de données optimisés
6. **Maintenance** : Code documenté et structuré

## 🔧 Configuration

### Variables d'environnement

- `WP_DEBUG` : Active les informations de debug
- `?debug_comportement` : Affiche les infos debug en footer

### Options WordPress

- `comportement_client_db_version` : Version de la base de données
- Nettoyage automatique programmé via `wp_schedule_event`

## 📈 Performances

### Optimisations incluses
- Index de base de données sur les colonnes importantes
- Requêtes SQL optimisées
- Chargement conditionnel des scripts admin
- Cache des informations de session

### Monitoring
- Logs des erreurs dans error_log WordPress
- Informations de debug pour les développeurs
- Statistiques de performance intégrées

## 🚀 Développement futur

### Fonctionnalités prévues
- API REST pour accès externe aux données
- Intégration avec Google Analytics
- Notifications en temps réel
- Export Excel et JSON

### Extensions possibles
- Géolocalisation des utilisateurs
- Analyse comportementale avancée
- Intégration CRM
- Tableau de bord client personnalisé

## 🐛 Dépannage

### Problèmes courants

1. **Tables non créées** : Vérifier les permissions MySQL
2. **Device non détecté** : Vérifier l'User-Agent du navigateur
3. **Interface admin manquante** : Vérifier les capacités utilisateur

### Debug

Ajouter `?debug_comportement=1` à l'URL pour voir les informations de debug.

### Support

- Vérifier les logs WordPress en cas d'erreur
- Utiliser `ComportementClientInit::obtenir_infos_debug()` pour diagnostiquer
- Vérifier la compatibilité des plugins tiers

## 📝 Licence

Ce code fait partie du thème WordPress Aleaulavage et suit les mêmes conditions de licence.