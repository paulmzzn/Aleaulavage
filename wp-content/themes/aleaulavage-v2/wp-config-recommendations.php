<?php
/**
 * Recommended wp-config.php settings for Aleaulavage V2
 *
 * Copy these settings to your wp-config.php file for optimal performance
 */

// ============================================
// PERFORMANCE OPTIMIZATIONS
// ============================================

// Enable object caching (requires a persistent cache like Redis or Memcached)
// define('WP_CACHE', true);

// Increase memory limit
define('WP_MEMORY_LIMIT', '256M');
define('WP_MAX_MEMORY_LIMIT', '512M');

// Limit post revisions (already set in theme, but can be overridden here)
define('WP_POST_REVISIONS', 3);

// Increase autosave interval (already set in theme, but can be overridden here)
define('AUTOSAVE_INTERVAL', 300); // 5 minutes

// Disable post revisions entirely (optional)
// define('WP_POST_REVISIONS', false);

// ============================================
// DATABASE OPTIMIZATIONS
// ============================================

// Enable advanced database repair
// define('WP_ALLOW_REPAIR', true); // Only enable when needed, then disable!

// Specify custom database charset
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', 'utf8mb4_unicode_ci');

// ============================================
// CRON OPTIMIZATIONS
// ============================================

// Disable WP-Cron and use system cron instead (recommended for production)
// define('DISABLE_WP_CRON', true);
// Then add this to your system crontab:
// */5 * * * * wget -q -O - http://localhost:8000/wp-cron.php?doing_wp_cron >/dev/null 2>&1

// ============================================
// DEBUGGING (DEVELOPMENT ONLY)
// ============================================

// Enable debug mode (DISABLE IN PRODUCTION!)
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
@ini_set('display_errors', 0);

// Enable script debug mode (loads non-minified CSS/JS)
define('SCRIPT_DEBUG', true); // Set to false in production

// Enable database query logging
// define('SAVEQUERIES', true); // WARNING: This slows down the site significantly!

// ============================================
// SECURITY
// ============================================

// Disable file editing from admin panel
define('DISALLOW_FILE_EDIT', true);

// Force SSL for admin (if using HTTPS)
// define('FORCE_SSL_ADMIN', true);

// ============================================
// WOOCOMMERCE OPTIMIZATIONS
// ============================================

// Disable WooCommerce admin features on frontend
// define('WC_ADMIN_DISABLED', true);

// Disable WooCommerce analytics
// define('WC_ANALYTICS_DISABLED', true);

// ============================================
// EXAMPLE FULL CONFIGURATION
// ============================================

/**
 * For production, your wp-config.php should look like this:
 *
 * // Performance
 * define('WP_CACHE', true);
 * define('WP_MEMORY_LIMIT', '256M');
 * define('WP_POST_REVISIONS', 3);
 * define('AUTOSAVE_INTERVAL', 300);
 *
 * // Cron
 * define('DISABLE_WP_CRON', true);
 *
 * // Security
 * define('DISALLOW_FILE_EDIT', true);
 * define('FORCE_SSL_ADMIN', true);
 *
 * // Debug (DISABLE IN PRODUCTION!)
 * define('WP_DEBUG', false);
 * define('SCRIPT_DEBUG', false);
 * define('SAVEQUERIES', false);
 */

// ============================================
// OBJECT CACHE SETUP (REDIS EXAMPLE)
// ============================================

/**
 * To use Redis for object caching:
 *
 * 1. Install Redis on your server
 * 2. Install the Redis Object Cache plugin
 * 3. Add this to wp-config.php:
 *
 * define('WP_REDIS_HOST', '127.0.0.1');
 * define('WP_REDIS_PORT', 6379);
 * define('WP_REDIS_DATABASE', 0);
 * define('WP_REDIS_CLIENT', 'phpredis'); // or 'predis'
 *
 * This will dramatically reduce database queries!
 */
