<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'shredders-and-shakers' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', 'root' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'gar0}iE#]:~ALp7UQudAs~}-61O,b~GW1Wj+R-H}/{9]LLb2 n(GEt?Ao99H!3]e' );
define( 'SECURE_AUTH_KEY',  ':P6r)2TzcBMH9rK)*{nCD7wZ7wRQ`-s`EE&:L@PHOY4kc_^^u=y0_Ha~{[j[-<2t' );
define( 'LOGGED_IN_KEY',    'H@U^GzR! w:.%?|M8{s*gK*[4(SEff{G(&rfP6Dug.>K$Xs ~Yy`=jQE|CjQQ,]D' );
define( 'NONCE_KEY',        'iVwxIoetu:A$7AOZv^%p<!.#i>Xr3d#>oz~}uSx(jf(.[B%c972lEc|ODGnvygx}' );
define( 'AUTH_SALT',        'Ru#~zeKAW]-%!lC0R<}CQ5vII:&5{mC0}A{pr37.g+h4@Qt6#C)#;5=aA5W-o`r3' );
define( 'SECURE_AUTH_SALT', '|$g^BINDoVRW=+I;35yG-|s{v,|_~ZJ W:n1Jy#LU[5{f ~pnB+.*JWn{dl~n85@' );
define( 'LOGGED_IN_SALT',   '*<pKR2Ag5chD?|lB)XxsewAa&6E9gqYa34YeDP^g82+o7o`9y?C%NMzg*F^~BR5j' );
define( 'NONCE_SALT',       'mCSOt`:TTU~HFRD]65?D4n(x7q3awOo*tm5rp{i4N{Wm)q1t[=`6z^QquC#b1o2v' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
