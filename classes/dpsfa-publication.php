<?php
/**
 *
 * Package: Folio Authoring for WordPress
 * Class : Publication
 * Description: This class contains publication specific parameters and functions.
 */
 
namespace DPSFolioAuthor;
 
if( $_SERVER[ 'SCRIPT_FILENAME' ] == __FILE__ )
	die( 'Access denied.' );

if(!class_exists('DPSFolioAuthor\Publication')) { 
    
    class Publication extends Entity{
	    
	    /* ADOBE FIELDS */
	    public $name;
	    public $title;
	    public $homeCollection;
	    public $socialSharingEnabled;
	    public $logo;
	    public $storeUrl;
	    public $analyticsId;
	    
	    /* PLUGIN FIELDS */
	    public $id;
	    public $permissions;
	    public $publications;
	    public $links;
	    
	    public function __construct() {
			parent::__construct();
		}
		
		public function save(){
		
		}
		
		public function get_all(){
		
		}
			        
    } // END class Publication 
}