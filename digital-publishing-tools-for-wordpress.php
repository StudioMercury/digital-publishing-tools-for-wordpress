<?php
/*
 *   Plugin Name: Digital Publishing Tools for WordPress
 *   Plugin URI: http://studiomercury.github.io/digital-publishing-tools-for-wordpress
 *   Description: Digital Publishing Tools for WordPress is a plugin that allows anyone to create articles for Adobe's Digital Publishing Solution directly from WordPress.
 *   Version: 2.0.10
 *   Author: Studio Mercury
 *   Author URI: http://studiomercury.github.io/digital-publishing-tools-for-wordpress
 *   Author Email: https://github.com/StudioMercury/digital-publishing-tools-for-wordpress/issues
 *   License: See license file
 */

namespace DPSFolioAuthor;

// Backwards __DIR__ compatibility:
if ( !defined('__DIR__') ) define('__DIR__', dirname(__FILE__));

// Generic Plugin Settings
define( 'DPSFA_VERSION',				'2.0.10' );
define( 'DPSFA_NAME',					'Digital Publishing Tools for WordPress' );
define( 'DPSFA_SHORT_NAME',				'Digital Publishing' );
define( 'DPSFA_SLUG',					'dps_folio_author' );
define( 'DPSFA_PREFIX',					'dpsfa_' );
define( 'DPSFA_SYNC_SUFFIX',			'_modtime' );
define( 'DPSFA_TIMEFORMAT',				'Y-m-d\TH:i:s' );
define( 'DPS_API_VERSION',				2.0); // Version of the API to use 1.0 or 2.0 (default)

// Requirements
define( 'DPSFA_DEBUGMODE',				FALSE );
define( 'DPSFA_REQUIRED_PHP_VERSION',	"5.3" );

// Choose CMS Connection
// The plugin is CMS independent. Available CMS modules include: `wordpress`
define( 'DPSFA_CMS',					'wordpress' ); // What CMS class to pull in
define( 'DPSFA_REQUIRED_CMS_VERSION',	'3.5' ); // What minimum CMS version to require
define( 'DPSFA_NONCE_KEY',				'DPSFolioAuthor' );

// Settings Encryption
define( 'DPSFA_ENCRYPTION_METHOD',		'AES-256-CBC' ); // Encryption method for settings
define( 'DPSFA_ENCRYPTION_BYTES',		16 ); // Encrypt method AES-256-CBC expects 16 bytes
define( 'DPSFA_HASH_ALGO',				'sha256' ); // Encrpytion Hash

// Plugin Assets and Directories
define( 'DPSFA_FILE',					__FILE__ );
define( 'DPSFA_DIR_NAME',               basename(__DIR__) );
define( 'DPSFA_DIR',					dirname(DPSFA_FILE) );
define( 'DPSFA_ASSETS_DIR',				DPSFA_DIR . "/assets/" );
define( 'DPSFA_ASSETS_URL',	            DPSFA_URL . "/assets/" );
define( 'DPSFA_VERSION_SLUG',	        DPSFA_SLUG . "_version" );
define( 'DPSFA_TMPDIR',					(substr(sys_get_temp_dir(), -1) == '/') ? sys_get_temp_dir() : sys_get_temp_dir() . "/" );

// VERSION 2.0 specific
define( 'DPS_API_AUTHENTICATION_END',	'https://ims-na1.adobelogin.com/' ); // Authentication server endpoints
define( 'DPS_API_AUTHORIZATION_END',	'https://authorization.publish.adobe.io/' ); // Authorization server endpoints
define( 'DPS_API_INGESTION_END',		'https://ings.publish.adobe.io/' ); // Ingestion server endpoints
define( 'DPS_API_PRODUCER_END',			'https://pecs.publish.adobe.io/' ); // Producer service endpoints
define( 'DPS_API_PRODUCT_END',			'https://ps.publish.adobe.io/' ); // Product service endpoints
define( 'DPS_PORTAL',					'https://publish.adobe.com/' ); // Publish Portal
define( 'DPS_API_CLIENT_VERSION', 		DPSFA_VERSION );
define( 'DPS_API_CLIENT_ID', 			'us.smny.folioauthor' ); // ie: dps_folio_author-wordpress_2.0

/* CONSTANTS FOR ENTITIES */
// ARTICLE
define( 'DPSFA_Article_Name', 			'Article' );
define( 'DPSFA_Article_Names', 			'Articles' );
define( 'DPSFA_Article_Slug', 			DPSFA_PREFIX . 'article' );
// BANNER
define( 'DPSFA_Banner_Name', 			'Banner' );
define( 'DPSFA_Banner_Names', 			'Banners' );
define( 'DPSFA_Banner_Slug', 			DPSFA_PREFIX . 'banner' );
// FOLIO
define( 'DPSFA_Folio_Name', 			'Folio' );
define( 'DPSFA_Folio_Names', 			'Folios' );
define( 'DPSFA_Folio_Slug', 			DPSFA_PREFIX . 'folio' );
// COLLECTION
define( 'DPSFA_Collection_Name', 		'Collection' );
define( 'DPSFA_Collection_Names', 		'Collections' );
define( 'DPSFA_Collection_Slug', 		DPSFA_PREFIX . 'collection' );
// PUBLICATION
define( 'DPSFA_Publication_Name', 		'Publication' );
define( 'DPSFA_Publication_Names', 		'Publications' );
define( 'DPSFA_Publication_Slug', 		DPSFA_PREFIX . 'publication' );
// PRODUCT
define( 'DPSFA_Product_Name', 			'Product' );
define( 'DPSFA_Product_Names', 			'Products' );
define( 'DPSFA_Product_Slug', 			DPSFA_PREFIX . 'product' );
// PRODUCT
define( 'DPSFA_Product_Bundle_Name', 	'Product Bundle' );
define( 'DPSFA_Product_Bundle_Names', 	'Product Bundles' );
define( 'DPSFA_Product_Bundle_Slug', 	DPSFA_PREFIX . 'product-bundle' );

// TODO:
// An option to cache folios.
// This allows publishers to download copies of their folios
// Additionally, it should speed up uploading of folios if content hasn't changed
define( 'DPSFA_CACHE_FOLIOS',	        false );
define( 'DPSFA_CACHE_ARTICLES',	        false );

// DEBUGGING
define( 'DPSFA_DEBUG_LOG',				DPSFA_DIR . "/log/debuglog.txt" );
// Enable error logging text file
if(DPSFA_DEBUGMODE){
	if(!file_exists(DPSFA_DEBUG_LOG)){
		try{
			$created = mkdir(DPSFA_DIR."/log", 0777);
			file_put_contents(DPSFA_DEBUG_LOG,"");
			chmod(DPSFA_DEBUG_LOG, 0777);
		}catch(Exception $e){
			"Can't create debug plugin";
		}
	}
	ini_set('log_errors_max_len', '0');
}

// Verify Server Requirements
dpsfa_server_requirements_met();

// Load CMS wrapper
load_cms_wrapper();

// Load required classes
require_once(  DPSFA_DIR . '/classes/dpsfa-error.php' );						// Class for extending PHP's error handling
require_once(  DPSFA_DIR . '/classes/dpsfa-error-logging.php' );				// Class for extending PHP's error logging
require_once(  DPSFA_DIR . '/classes/dpsfa-curl.php' );							// Class for making cURL calls
require_once(  DPSFA_DIR . '/classes/dpsfa-adobe.php' );						// Class for the Adobe DPS API
require_once(  DPSFA_DIR . '/classes/dpsfa-settings.php' );						// Class for settings of the plugin
require_once(  DPSFA_DIR . '/classes/dpsfa-bundlr.php' );						// Class for Bundling Adobe Folios / Articles
require_once(  DPSFA_DIR . '/classes/dpsfa-sidecar.php' );						// Class for Sidecar XML Importer & Exporter
require_once(  DPSFA_DIR . '/classes/dpsfa-templates.php' );                    // Class for Template Management calls
require_once(  DPSFA_DIR . '/classes/dpsfa-sync.php' );                    		// Class for Syncing Entities and CMS content


require_once(  DPSFA_DIR . '/classes/dpsfa-entity.php' );						// Class for Adobe Publish Entity
require_once(  DPSFA_DIR . '/classes/dpsfa-content.php' );						// Class for Adobe Publish Entity - Content
require_once(  DPSFA_DIR . '/classes/dpsfa-article.php' );						// Class for Adobe Publish Entity - Article
// require_once(  DPSFA_DIR . '/classes/dpsfa-collection.php' );					// Class for Adobe Publish Entity - Collection
// require_once(  DPSFA_DIR . '/classes/dpsfa-publication.php' );					// Class for Adobe Publish Entity - Publication

/*
require_once(  DPSFA_DIR . '/classes/dpsfa-ajax.php' );                           // Class for Ajax calls
require_once(  DPSFA_DIR . '/classes/dpsfa-device.php' );                         // Class for Devices
require_once(  DPSFA_DIR . '/classes/dpsfa-folio.php' );                          // Class for Folios
require_once(  DPSFA_DIR . '/classes/dpsfa-filter.php' );                         // Class for Filtering articles and folios
require_once(  DPSFA_DIR . '/classes/dpsfa-template-renderer.php' );              // Class for Template Rendering
require_once(  DPSFA_DIR . '/classes/dpsfa-update.php' );                         // Class for Checking for Updates
require_once(  DPSFA_DIR . '/classes/dpsfa-sync.php' );                           // Class for Syncing changes among articles and folios
require_once(  DPSFA_DIR . '/classes/dpsfa-views.php' );                          // Class for Rendering Views
require_once(  DPSFA_DIR . '/classes/dpsfa-utilities.php' );                      // Class for Generic Utilities
*/

function dpsfa_server_requirements_met(){
	// Look at requirement for PHP
	if( version_compare( PHP_VERSION, DPSFA_REQUIRED_PHP_VERSION, '<' ) ){
		$message = "DPSFA - Initialization: Failed. Requires PHP v".DPSFA_REQUIRED_PHP_VERSION." but found v".PHP_VERSION;
		if(DPSFA_DEBUGMODE) log_message($message);
		die($message);
	}

	if(DPSFA_DEBUGMODE){
		$message = "Checking server requirements. Looking for v".DPSFA_REQUIRED_PHP_VERSION." and found v".PHP_VERSION.".";
		if(DPSFA_DEBUGMODE) log_message($message);
	}
}

// Function for loading the CMS wrapper
function load_cms_wrapper(){
	// Load CMS Class
	if(file_exists(DPSFA_DIR . '/classes/cms-modules/' . DPSFA_CMS . '/dpsfa-cms-'.DPSFA_CMS.'.php')){
		require_once( DPSFA_DIR . '/classes/cms-modules/' . DPSFA_CMS . '/dpsfa-cms-'.DPSFA_CMS.'.php' );
		$CMS = new CMS();
		// Verify CMS Version
		if( version_compare( $CMS->get_cms_version(), DPSFA_REQUIRED_CMS_VERSION, '<' ) ){
			$message = "DPSFA - Loading CMS: Failed. Requires ".DPSFA_CMS." v".DPSFA_REQUIRED_CMS_VERSION." but found v".$CMS->get_cms_version();
			if(DPSFA_DEBUGMODE) log_message($message);
			die($message);
		}
		$CMS->init();
	}else{
		$message = "Unable to load the CMS Wrapper (".DPSFA_CMS."). Please make sure you have set the correct CMS Wrapper in bootstrap.php before continuing";
		if(DPSFA_DEBUGMODE) log_message($message);
		die($message);
	}

	if(DPSFA_DEBUGMODE){
		$message = "CMS module ".DPSFA_CMS." (".DPSFA_CMS.") loaded successfully. Looking for v".DPSFA_REQUIRED_CMS_VERSION." and found v".$CMS->get_cms_version();
		log_message($message);
	}
}

// Debugging function
// To use: if(DPSFA_DEBUGMODE){ log_message(""); }
function log_message($message) {
	$save = "[".date(DATE_RFC2822)."] " . $message;
	if(is_writable(DPSFA_DEBUG_LOG))
		error_log($save . "\n", 3, DPSFA_DEBUG_LOG);
}

function log_var( $object = null ){
    ob_start();                    // start buffer capture
    var_dump( $object );           // dump the values
    $contents = ob_get_contents(); // put the buffer into a variable
    ob_end_clean();                // end capture
    return $contents;
}