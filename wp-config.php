<?php

// BEGIN iThemes Security - Ne pas modifier ou supprimer cette ligne
// iThemes Security Config Details: 2
define( 'DISALLOW_FILE_EDIT', true ); // Désactiver l’éditeur de fichier - Sécurité > Réglages > Modifications de WordPress > Éditeur de fichier
define( 'FORCE_SSL_ADMIN', true ); // Redirect All HTTP Page Requests to HTTPS - Security > Settings > Secure Socket Layers (SSL) > SSL for Dashboard
// END iThemes Security - Ne pas modifier ou supprimer cette ligne

define('COOKIE_DOMAIN', ''); // Ajouté par W3 Total Cache


/** Enable W3 Total Cache */

define('WP_CACHE', true); // Added by W3 Total Cache


/**
 * La configuration de base de votre installation WordPress.
 *
 * Ce fichier contient les réglages de configuration suivants : réglages MySQL,
 * préfixe de table, clés secrètes, langue utilisée, et ABSPATH.
 * Vous pouvez en savoir plus à leur sujet en allant sur
 * {@link http://codex.wordpress.org/fr:Modifier_wp-config.php Modifier
 * wp-config.php}. C’est votre hébergeur qui doit vous donner vos
 * codes MySQL.
 *
 * Ce fichier est utilisé par le script de création de wp-config.php pendant
 * le processus d’installation. Vous n’avez pas à utiliser le site web, vous
 * pouvez simplement renommer ce fichier en "wp-config.php" et remplir les
 * valeurs.
 *
 * @package WordPress
 */

// ** Réglages MySQL - Votre hébergeur doit vous fournir ces informations. ** //
/** Nom de la base de données de WordPress. */
define('DB_NAME', '');

/** Utilisateur de la base de données MySQL. */
define('DB_USER', '');

/** Mot de passe de la base de données MySQL. */
define('DB_PASSWORD', '');

/** Adresse de l’hébergement MySQL. */
define('DB_HOST', '');

/** Jeu de caractères à utiliser par la base de données lors de la création des tables. */
define('DB_CHARSET', '');

/** Type de collation de la base de données.
  * N’y touchez que si vous savez ce que vous faites.
  */
define('DB_COLLATE', '');

/**#@+
 * Clés uniques d’authentification et salage.
 *
 * Remplacez les valeurs par défaut par des phrases uniques !
 * Vous pouvez générer des phrases aléatoires en utilisant
 * {@link https://api.wordpress.org/secret-key/1.1/salt/ le service de clefs secrètes de WordPress.org}.
 * Vous pouvez modifier ces phrases à n’importe quel moment, afin d’invalider tous les cookies existants.
 * Cela forcera également tous les utilisateurs à se reconnecter.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'QD4~BYcR[M+L?Hd/=C&1pl;e>Cat{]Wt&5c<F5*qv__B}L&ywk!^:X^qC9Y]$vHd');
define('SECURE_AUTH_KEY',  '789G$q>0L4@0lzo K?Pm2zx1UsS{P=Vlt&=y98T=-SU|`5{<RJ2bD%1;-N?&%/io');
define('LOGGED_IN_KEY',    '!$l<*0.C$Uextm^5y`_9w@Sl8&O8nfhMEV`<R(HhB/Q{{;!?Q*yih7$sho=tLG+C');
define('NONCE_KEY',        'XC`2wi~&z32u@U.0Y/dS:U$`ts)p*;8HJ/NR)`~S53[s6y2z6{[?4|alN>I!/l 0');
define('AUTH_SALT',        ')AK[Fhl:]5@i9Pg>,0zk-?m3tlCg|6;!G,$[[l<yW?>!#0RUilpI;YE};WZ#D2W@');
define('SECURE_AUTH_SALT', 'Bg a7vta6A*NIM]H^zZ[S!`|QBZajc>J!vZ3PqK-=C071>0$AbW~,D)#j)ze }Sg');
define('LOGGED_IN_SALT',   'L?g:E(dlb~F+hqYA7##z`Nn-w|aOI&/h2fHX{D|fX$TG04Vn^MH|(wD>Giob1{(~');
define('NONCE_SALT',       '*6%hj6k`2+q}~rIn}i<gXDQ92$U w O[:LQR*]k7SvPQb+^B6,v?Ueu;Q)iT?h9p');
/**#@-*/

/**
 * Préfixe de base de données pour les tables de WordPress.
 *
 * Vous pouvez installer plusieurs WordPress sur une seule base de données
 * si vous leur donnez chacune un préfixe unique.
 * N’utilisez que des chiffres, des lettres non-accentuées, et des caractères soulignés !
 */
$table_prefix  = '';

/**
 * Pour les développeurs : le mode déboguage de WordPress.
 *
 * En passant la valeur suivante à "true", vous activez l’affichage des
 * notifications d’erreurs pendant vos essais.
 * Il est fortemment recommandé que les développeurs d’extensions et
 * de thèmes se servent de WP_DEBUG dans leur environnement de
 * développement.
 *
 * Pour plus d’information sur les autres constantes qui peuvent être utilisées
 * pour le déboguage, rendez-vous sur le Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', true);

/*Increase memory ram front*/
define('WP_MEMORY_LIMIT', '256M');

/*Increase memory ram admin*/
define( 'WP_MAX_MEMORY_LIMIT', '512M' );

/*Revision limit*/
define('WP_POST_REVISIONS', 2);

/* C’est tout, ne touchez pas à ce qui suit ! */

/** Chemin absolu vers le dossier de WordPress. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Réglage des variables de WordPress et de ses fichiers inclus. */
require_once(ABSPATH . 'wp-settings.php');