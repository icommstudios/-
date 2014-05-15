<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

@ini_set('log_errors','On');
@ini_set('display_errors','Off');
//@ini_set('error_reporting', E_ERROR);
@ini_set('error_log', dirname(__FILE__)  . '/wp-content/error_logs.txt');


// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('WP_CACHE', true); //Added by WP-Cache Manager
define( 'WPCACHEHOME', '/home/alliedfa/public_html/wp-content/plugins/wp-super-cache/' ); //Added by WP-Cache Manager
define('DB_NAME', 'alliedfa_afs');

/** MySQL database username */
define('DB_USER', 'alliedfa_afs');

/** MySQL database password */
define('DB_PASSWORD', '+oJ.#)=N,$S&');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

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
define('AUTH_KEY',         'bQG]TAHpwS%f:VC(ID^>+}t-t5DCPWQ=|Mz3Nx-LF-(XlGz2 !Fqiku!Qt42)rq|');
define('SECURE_AUTH_KEY',  '5&0AR%5z0|+S7I9c-qtTG^nbE/g}b.Jd@Nh(gnu{g,&_m^{r5|%GBxMEA]Mo`!Hz');
define('LOGGED_IN_KEY',    'xFI`O`+AQK_dxNKI|aSTi0HHcU5)!hBO!h$%>(:[76S4E9<,XHh{az(tI#]U5G<^');
define('NONCE_KEY',        '~#d;mc=8uWNr$a4m:4lB&k;_c9}=Z~mXR;a6s0f(GgNZ6C2J!wT:~CqDUrJA-#+p');
define('AUTH_SALT',        '?*Pkq`%T0-gyUizTn>h^zn=e+hWTWF<`k&|4HHd!4C%<ov}>e0*IzXj k[IMUqsc');
define('SECURE_AUTH_SALT', 'Q.!6ikdX02#Y)(Yg~U%eR#7`,m2&{<BjoJ-`hG:fRLDZA_r_7jrHC>0G@zfUr*A-');
define('LOGGED_IN_SALT',   ']*+|z|p3mOcK+PNCaVKI|c!5Ki(%-Ln+R$e{9?$.[n>bY|B7)}0idR{{jh@3~wW4');
define('NONCE_SALT',       'O;thq6kt25U@N>pO.)(Y]<!>PLl_;Us2!P+n-?(c$0MR)jbBePQIQR!d+*ojD=_m');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'aFS857_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
