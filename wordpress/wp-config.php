<?php
/** 
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information by
 * visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'aps57447a29e89da');

/** MySQL database username */
define('DB_USER', 'aps57447a29e8cb7');

/** MySQL database password */
define('DB_PASSWORD', '0837a7889c2622b9');

/** MySQL hostname */
define('DB_HOST', 'localhost:3306');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link http://api.wordpress.org/secret-key/1.1/ WordPress.org secret-key service}
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'UqTqNMqD!hO*)*4s(yNd*CJpuTZhVFJ#&u1oj2lVWqQik4wy5XJ&mGZf)wmg5y42');
define('SECURE_AUTH_KEY',  'gCPLB0%y*hs*)ngq4ciN@0aWdQmtdg!BkHPOYlZb19(Qwe9Yq#*4qEPm^k!!R6l&');
define('LOGGED_IN_KEY',    'mp6y@J#tbvGQQd1vYkN(97mI#KJe6#XE0*Mx)jaoK#Z8UgH!#C0DL#SyPD^QBDe%');
define('NONCE_KEY',        'N7jFNRh8XG3NVI@9OphB8^oHv%)Lw*bakJrzpB$OupAwngBoUy7tbOfpjlgt12ah');
define('AUTH_SALT',        '^hH7h6uy(D&YH9v!5@NL8MKq%xhyQdQo9(CCpo3Ytbft)MgFRupNJ4KR^mYYBon8');
define('SECURE_AUTH_SALT', 'cbh4G8(xk%3FtzCDSV0KO%ZITZ(1kT7gOBn5NkckC4o@Wy(1)PIIKmvYKTPBXSc6');
define('LOGGED_IN_SALT',   '1zz!afHAYz8krZnR6fCFOPjrnuftvgpKOS%6iZ7C9Yz@rgrXlpz%b#wVult$9PQP');
define('NONCE_SALT',       'OIyu0yFLTrt(92h0$Q#gT&w%az)CVxq26T5oXs!JVf3zQ7h%*tqz5)BM0eN#rXnB');
/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress.  A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de.mo to wp-content/languages and set WPLANG to 'de' to enable German
 * language support.
 */
define ('WPLANG', 'en_US');

define ('FS_METHOD', 'direct');

define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** WordPress absolute path to the Wordpress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');

//--- disable auto upgrade
define( 'AUTOMATIC_UPDATER_DISABLED', true );



?>
