<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'world' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         ',QaCHDrQW/`rM .oj(3ypN+.;::2BCs#)27AiC2z$$ZzE+HOt?GkS.nXbU%^;6K>' );
define( 'SECURE_AUTH_KEY',  'tvP&aw;r7e(tI,,va-UeV`z,dZ`hwCIEmBGg_9-lLzI7{0%wEC(,Dv,`{8hdkW*X' );
define( 'LOGGED_IN_KEY',    'XyA]m]g!+D|7<~k&:2]4~_YpbuKOZ2*1!cK,2n:$xjv$#|>vkH1V0[k`6}SXf w~' );
define( 'NONCE_KEY',        'N1]g$%Tt6g:0|+8]Ecv/[Epo@/E!M1#%KW@m!O4Ftn=phdD8%W|*8xQl*@?T>;wQ' );
define( 'AUTH_SALT',        '0T*J:({B<JR`Ybf6qbup~iiX7{kR`}iKXy!T:A$!{2^3e1AY(T9RTDh6Z6[?{bVx' );
define( 'SECURE_AUTH_SALT', ' EoX0oKcR?k<3oe5uIy&=!wWTwF6a}065-,c#lS}:C.S_ RB.$.7@6[&[f0!P^@T' );
define( 'LOGGED_IN_SALT',   '{$JXeNoy~G*D$eZ.?`~(8`E(Mb>QimGPMGs(f(QYRbCyTZ&)P.>X]-@%85s>R%;0' );
define( 'NONCE_SALT',       'KRPYuUw`4nI*tlRZBUhwG4}Dd(8.Ad1Y +`Yz#akNAOvG`&a77(CHJ23@` 7wZa6' );

/**#@-*/

/**
 * WordPress database table prefix.
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
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
