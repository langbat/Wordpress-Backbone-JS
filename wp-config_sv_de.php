<?php

/**
 *
 * Secret-Keys, Sprache und ABSPATH. Mehr Informationen zur wp-config.php gibt es auf der {@link http://codex.wordpress.org/Editing_wp-config.php
 *
 * und die Installationsroutine (/wp-admin/install.php) aufgerufen wird.
 * Man kann aber auch direkt in dieser Datei alle Eingaben vornehmen und sie von wp-config-sample.php in wp-config.php umbenennen und die Installation starten.
 *
 * @package WordPress
 */
// error_reporting(E_ALL);
/**  MySQL Einstellungen - diese Angaben bekommst du von deinem Webhoster. */
define('DB_NAME', 'd0185f8f');

/** Ersetze username_here mit deinem MySQL-Datenbank-Benutzernamen */
define('DB_USER', 'd0185f8f');

/** Ersetze password_here mit deinem MySQL-Passwort */
define('DB_PASSWORD', 'praktischArzt');

/** Ersetze localhost mit der MySQL-Serveradresse */
define('DB_HOST', 'prakt.user-interaction.de');

/** Der Datenbankzeichensatz der beim Erstellen der Datenbanktabellen verwendet werden soll */
define('DB_CHARSET', 'utf8');

define('DB_COLLATE', '');

/**#@+
 *
 * Auf der Seite {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service} kannst du dir alle KEYS generieren lassen.
 *
 * @seit 2.6.0
 */
define('AUTH_KEY',         '+kx{:`+yF!:[$Xh:5b[SMX7&b4-GFHGN++-*G0<kh&JQa,U%l2rk!hVhhQ41w,[0');
define('SECURE_AUTH_KEY',  'Wxa:NgyuQ`AWc8sUCOwwM_|Fe]v:d@@rTNT$j)2BYAM&:*--@+Z,bo%`i 08lk;x');
define('LOGGED_IN_KEY',    '.wny4dTkkm9NR9_Q-_)#Hxu;+LH=d1;3`?p*5PIO|Emv<#U31y_1#!GLkqG#i F[');
define('NONCE_KEY',        '|Qej|^{$@UsTiG5|$%2[_|nu4N|}SBGgyT&{$[kF}1oX9-|*lf@RVb[,qV-5D|[:');
define('AUTH_SALT',        'I-[;K2;E uJQ<@0fg]jb^`usBvN#Ot_+50m0V~d~_:T mET#Ki2f74<08-CPLGw[');
define('SECURE_AUTH_SALT', 'fVMH^a^g}J`!4mbJCcvN]PQv,zYCi1~MvQa/M]5k^Kn-/&mHz/=]shWMJd3|WSA$');
define('LOGGED_IN_SALT',   'W{)#C]]-1v(kI[4ncL!lMja#[IujqAYgC`Dbl[,g{2IWKX-4px|xl,gWD?GSjmMf');
define('NONCE_SALT',       'Fw||egi+t1V@WpXG<Hqx46M`@hb|#AEtDS&m:+k*v63bzX,7g8NaL^/uzqf 4gq<');

/**#@-*/

/**
 *
 *  verschiedene WordPress-Installationen betreiben. Nur Zahlen, Buchstaben und Unterstriche bitte!
 */
$table_prefix  = 'dev03';

/**
 * WordPress Sprachdatei
 *
 * Hier kannst du einstellen, welche Sprachdatei benutzt werden soll. Die entsprechende
 * Sprachdatei muss im Ordner wp-content/languages vorhanden sein, beispielsweise de_DE.mo
 */
define('WPLANG', 'de_DE');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', true);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');