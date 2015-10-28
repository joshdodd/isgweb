<?php
ini_set('display_errors','off');
# Database Configuration
define( 'DB_NAME', 'snapshot_mlinson' );
define('DB_USER','mlinson');
define('DB_PASSWORD','49XKR1Vt7sjuAcUfgd3i');
define( 'DB_HOST', '127.0.0.1' );
define( 'DB_HOST_SLAVE', '127.0.0.1' );
define('DB_CHARSET', 'utf8');
define('DB_COLLATE', 'utf8_unicode_ci');
$table_prefix = 'wp_';

# Security Salts, Keys, Etc
define('AUTH_KEY',         'V6y59FZqYVe-,*frK,>0t%b?,+>?:]Kd@b/8~e([uNXie{,2Ky SDa#{L.-)ZVu$');
define('SECURE_AUTH_KEY',  '@WVb)156`XBFh85jHu/,`r-+A4./fEd= c+MkqbTXYe.h(-rp&90}Uyn|=nX{$eY');
define('LOGGED_IN_KEY',    'dhAHur<e%fPAYZlpuf@^oD^s5UNUJ/HFx7;w>-y^7+&R*}6U H5etO#pmL$D)N7d');
define('NONCE_KEY',        'BVu^qw68R78NJ.Lf|5yi!J+[|e&}Eg21`|bGXg|9e77tcZ%7X(C#|}))J.`&-1%?');
define('AUTH_SALT',        'G)YxW1?7dg<rSJOs -k.e+Hjs2&|0fn:;dWp[)(p,a?5VCHi1FxF.tau0jhxnacP');
define('SECURE_AUTH_SALT', 'l;=5=-^jAz|Uan5K6iSo)*m3v4M9-e=OpS_vtY!4u>;|PYBhVt7^Xd>7mj,4!X@U');
define('LOGGED_IN_SALT',   '!<?%-KK%IS?X++nqbY:Ez86jyK-]q3T%sr4@LaM5p*6=4?(MfpMUoCk3tD.gBu,^');
define('NONCE_SALT',       '+;(A-v3^8%nWd{_vzZ63B%L9t.<mN/De-n0*e4}#Cv|X=&4rEpc}<4GOA] H0o;c');


# Localized Language Stuff

define('WP_CACHE',TRUE);

define('PWP_NAME','mlinson');

define('FS_METHOD','direct');

define('FS_CHMOD_DIR',0775);

define('FS_CHMOD_FILE',0664);

define('PWP_ROOT_DIR','/nas/wp');

define( 'WPE_APIKEY', '38d04894f089887343f9b31ab93a026807a41d7b' );

define('WPE_FOOTER_HTML',"");

define('WPE_CLUSTER_ID','2197');

define('WPE_CLUSTER_TYPE','pod');

define('WPE_ISP',true);

define('WPE_BPOD',false);

define('WPE_RO_FILESYSTEM',false);

define('WPE_LARGEFS_BUCKET','largefs.wpengine');

define('WPE_CDN_DISABLE_ALLOWED',false);

define('DISALLOW_FILE_EDIT',FALSE);

define('DISALLOW_FILE_MODS',FALSE);

define('DISABLE_WP_CRON',false);

define( 'WPE_FORCE_SSL_LOGIN', false );

define( 'FORCE_SSL_LOGIN', false );

/*SSLSTART*/ if ( isset($_SERVER['HTTP_X_WPE_SSL']) && $_SERVER['HTTP_X_WPE_SSL'] ) $_SERVER['HTTPS'] = 'on'; /*SSLEND*/

define( 'WPE_EXTERNAL_URL', false );

define('WP_POST_REVISIONS',FALSE);

define('WPE_WHITELABEL','wpengine');

define('WP_TURN_OFF_ADMIN_BAR',false);

define('WPE_BETA_TESTER',false);

umask(0002);

$wpe_cdn_uris=array ();

$wpe_no_cdn_uris=array ();

$wpe_content_regexs=array ();

$wpe_all_domains=array (  0 => 'mlinson.wpengine.com',  1 => 'essentialhospitals.org',  2 => 'www.essentialhospitals.org',  3 => 'naph.org',  4 => 'www.naph.org',  5 => 'support.mlinson.wpengine.com',);

$wpe_varnish_servers=array (  0 => 'pod-2197',);

$wpe_ec_servers=array ();

$wpe_largefs=array ();

$wpe_netdna_domains=array (  0 =>   array (    'match' => 'mlinson.wpengine.com',    'zone' => '46wzm04cs2161a4k7hfp3dhjek',    'secure' => false,    'enabled' => true,  ),  1 =>   array (    'match' => 'essentialhospitals.org',    'zone' => '2c4xez132caw2w3cpr1il98fssf',    'enabled' => true,  ),);

$wpe_netdna_push_domains=array ();

$wpe_domain_mappings=array ();

$memcached_servers=array (  'default' =>   array (    0 => 'unix:///tmp/memcached.sock',  ),);

define( 'WP_SITEURL', 'http://mlinson.staging.wpengine.com' );

define( 'WP_HOME', 'http://mlinson.staging.wpengine.com' );

define('WP_AUTO_UPDATE_CORE',false);

$wpe_special_ips=array ();

$wpe_netdna_domains_secure=array ();

define( 'DOMAIN_CURRENT_SITE', 'mlinson.staging.wpengine.com' );
define('WPLANG','');
//error_reporting(0);
define('WP_DEBUG_DISPLAY', true);
define('WP_POST_REVISIONS',FALSE);
define('WP_POST_REVISIONS',FALSE);
define( 'WP_DEBUG', false );
error_reporting(E_ALL ^ E_WARNING); 

# WP Engine ID


# WP Engine Settings




/* Multisite */
/*define( 'WP_ALLOW_MULTISITE', true );
define('MULTISITE', true);
define('SUBDOMAIN_INSTALL', true);

define('PATH_CURRENT_SITE', '/');
define('SITE_ID_CURRENT_SITE', 1);
define('BLOG_ID_CURRENT_SITE', 1);*/


# That's It. Pencils down
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');
require_once(ABSPATH . 'wp-settings.php');

$_wpe_preamble_path = null; if(false){}
