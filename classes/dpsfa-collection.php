<?php
/**
 *
 * Package: Folio Authoring for WordPress
 * Class : Collection
 * Description: This class contains collection specific parameters and functions.
 */
 
namespace DPSFolioAuthor;
 
if( $_SERVER[ 'SCRIPT_FILENAME' ] == __FILE__ )
	die( 'Access denied.' );

if(!class_exists('DPSFolioAuthor\Collection')) { 
    
    class Collection extends Content{
	    
	    public $isIssue = TRUE;
	    public $allowDownload = FALSE;
	    public $openTo = "browsePage";
	    public $readingPosition = "retain";
	    public $maxSize = -1;
	    public $lateralNavigation = TRUE;
	    public $background;
	    public $contentElements;
	    public $view;
	    public $coverDate;
	    
	    public function __construct() {
			parent::__construct();
		}
			        
    } // END class Collection 
}