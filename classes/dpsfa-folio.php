<?php
/**
 *
 * Package: Folio Authoring for WordPress
 * Class : Folio
 * Description: This class contains folio specific parameters and functions.
 */
 
namespace DPSFolioAuthor;
 
if( $_SERVER[ 'SCRIPT_FILENAME' ] == __FILE__ )
	die( 'Access denied.' );

if(!class_exists('DPSFolioAuthor\Folio')) { 
    
    class Folio extends Content{
	    
	    public $cover_h = "";
	    public $cover_v = "";
	    public $folioNumber = "";
	    public $publicationDate = "";
	    public $coverDate = "";
	    public $magazineTitle = "";
	    
	    public function __construct() {
			parent::__construct();
		}
			        
    } // END class Folio 
}