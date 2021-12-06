<?php
/*
 * Don't show deprecations
 */
error_reporting( E_ALL ^ E_DEPRECATED );

/**
 * Define site and home URLs
 */
// HTTP is still the default scheme for now.
$scheme = 'http';
// If we have detected that the end use is HTTPS, make sure we pass that
// through here, so <img> tags and the like don't generate mixed-mode
// content warnings.
if ( isset( $_SERVER['HTTP_USER_AGENT_HTTPS'] ) && $_SERVER['HTTP_USER_AGENT_HTTPS'] == 'ON' ) {
	$scheme = 'https';
}
$site_url = getenv( 'WP_HOME' ) !== false ? getenv( 'WP_HOME' ) : $scheme . '://' . $_SERVER['HTTP_HOST'] . '/';
define( 'WP_HOME', $site_url );
define( 'WP_SITEURL', $site_url . 'wp/' );

/**
 * Set Database Details
 */
define( 'DB_NAME', getenv( 'DB_NAME' ) );
define( 'DB_USER', getenv( 'DB_USER' ) );
define( 'DB_PASSWORD', getenv( 'DB_PASSWORD' ) );
define( 'DB_HOST', getenv( 'DB_HOST' ) );
define( 'DB_PORT', getenv( 'DB_PORT' ) );

/**
 * Set debug modes
 */
define( 'WP_DEBUG', getenv( 'WP_DEBUG' ) );
define( 'IS_LOCAL', getenv( 'IS_LOCAL' ) );
define( 'SCRIPT_DEBUG', getenv( 'SCRIPT_DEBUG' ) );
define( 'WP_DEBUG_DISPLAY', getenv( 'WP_DEBUG_DISPLAY' ) );
define( 'WP_DEBUG_LOG', getenv( 'WP_DEBUG_LOG' ) );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY', getenv( 'AUTH_KEY' ) );
define( 'SECURE_AUTH_KEY', getenv( 'SECURE_AUTH_KEY' ) );
define( 'LOGGED_IN_KEY', getenv( 'LOGGED_IN_KEY' ) );
define( 'NONCE_KEY', getenv( 'NONCE_KEY' ) );
define( 'AUTH_SALT', getenv( 'AUTH_SALT' ) );
define( 'SECURE_AUTH_SALT', getenv( 'SECURE_AUTH_SALT' ) );
define( 'LOGGED_IN_SALT', getenv( 'LOGGED_IN_SALT' ) );
define( 'NONCE_SALT', getenv( 'NONCE_SALT' ) );
