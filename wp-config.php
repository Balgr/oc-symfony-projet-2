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
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'chalets_et_caviar');

/** MySQL database username */
define('DB_USER', 'chalets_et_caviar');

/** MySQL database password */
define('DB_PASSWORD', '');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '0BJ.po#{Ksjwrh;zr{CgMo&cR]>K?nIk{X+oa&!goTo]2j}Wbs&sIM6=*!aS#uUk');
define('SECURE_AUTH_KEY',  '=al`euN.zhAwW$6/N#lbZztvqQr}2-nt#*:r.p~%oOFI9t~o9T=ebT})yNna,SX9');
define('LOGGED_IN_KEY',    'm73=j|kR5%&dSBWd+<GBdZasw8f>I iE^MRpw0VN RWIDF4N8e$^Mb,,mtSAl-PC');
define('NONCE_KEY',        'dim]<<aLLd714*AKk*5S%/$Aav(Uk}/Xl)B8$Qn<<g*{Vf]B6m8}g|=V|X)1unGu');
define('AUTH_SALT',        'R3DDG[AL2V2VAB~-z(*Z U*ez$BwWk85pmFcZX}0.6cH2nM.({q$VF5Cs~^J5lB$');
define('SECURE_AUTH_SALT', ',aqt$H_LHTNP`bV{._h.n?6N`5]BB4UYb2G,%u8<$?njrs|$-OC0ysT*x`83,o%z');
define('LOGGED_IN_SALT',   '&2gY]x$Zme<T|5 [|3L[3Uc5RSJcJJt!3<cQCp4-B*rfzUNLbmj=/U!Is80@M:t-');
define('NONCE_SALT',       '(v>!%h_}L`rNQ}F%Fn0>w?EcUCQJoeJ=R#B[cy.-7wFkG^{YgV2,%}s@FLl[{}Ze');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
