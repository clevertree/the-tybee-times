<?php
define("FS_METHOD", 'direct');

/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, and ABSPATH. You can find more information by visiting
 * {@link http://codex.wordpress.org/Editing_wp-config.php Editing wp-config.php}
 * Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'tybeetimes');

/** MySQL database username */
define('DB_USER', 'tybeetimes');

/** MySQL database password */
define('DB_PASSWORD', 'YwQ5BUJ7w');

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
define('AUTH_KEY',         'OBI$Z^)(HWuYwNgv!:/n^ALy!fj0ne1EDQ|m:@N>WUkL?IXwBS/MJU`-ed+&.9,j');
define('SECURE_AUTH_KEY',  'w^/gnl`e`;BXEt<9yp-H7`x_SiS.v@S@R78Y)k&DF>fqLsGfEH~J rfRY4<PLQ(2');
define('LOGGED_IN_KEY',    'm3<tfV8;|%Qz[S.zzj;c2XaF*4X0-~mKr~.J`*v6x1|w;vjdiu)6SmuzwY+IMp9$');
define('NONCE_KEY',        '++q|@HWt<Qtq#`wI:1J`p:#fFTqml-RXMh#~?4)q0DX~CD1aXcwrP]2ClqoAv@jR');
define('AUTH_SALT',        'CQDR|EVJw;lb*&J&uqQlqdvF,EKTCu+#DB5aNGue@</}V}Ui#Y#Wu:+nGs^DD4E1');
define('SECURE_AUTH_SALT', 'Z|-[RX}e@76/3`FilT,s;pXx:Ii<{aj>|EPa_;1tZg9&[nMNl7EYa+yck{+!7j3i');
define('LOGGED_IN_SALT',   'E)r|;25!I.u6p_l]znn#4%W7)|U.q3CJ %&iB|+W=T|~IT*[7<J{!iPy!A8Cg][E');
define('NONCE_SALT',       'wVGM[_@oqJ!EK|Mh-csw-D:`xTr:{2vua0 o}!9;eUri(CV3n$_(MsRb(GP0-bwY');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

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
