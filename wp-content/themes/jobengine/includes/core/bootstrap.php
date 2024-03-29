<?php
/**
 * Define some static variables
 */
define ( 'FRAMEWORK_PATH', dirname(__FILE__) );
define ( 'FRAMEWORK_URL', get_bloginfo('template_url') . '/includes/core' );
define ( 'ET_DOMAIN' , 'enginetheme');

/**
 * Declare global variables for engine theme
 */
global $et_global;
$et_global = array(
	'page_templates' 		=> array(),
	'notices' 				=> array(),
	'engine_admin_pages'	=> array(),
	'user_fields' 			=> array(),
	'validator' 			=> false,
	'post_fields' 			=> false,
	'db_prefix' 			=> 'et_',
	'jsUrl'					=>	get_bloginfo('template_url').'/js/',
	'imgUrl'					=>	get_bloginfo('template_url').'/img/',
);

/**
 * Auto include all library functions and classes
 */
$paths = array(
	'base' => array(
		'class-object',
		'customization',
		'class-master',
		'class-authentication',
		'class-admin-menu',
		'mobile-detect',
		'template',
		'validation',
		'class-options',
		'parsers',
		'class-importer',
		'languages'
	)
);

foreach ($paths as $folder => $files) {
	foreach ($files as $file) {
		if ( $folder == 'base' )
		{
			if ( file_exists( FRAMEWORK_PATH . '/' . $file . '.php' ) ){
				require_once( FRAMEWORK_PATH . '/' . $file . '.php' );
			}
		}
		else {
			if ( file_exists( FRAMEWORK_PATH . '/' . $folder . '/' . $file . '.php' ) ){
				require_once( FRAMEWORK_PATH . '/' . $folder . '/' . $file . '.php' );
			}
		}
	}
}
?>