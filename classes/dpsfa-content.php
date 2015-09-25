<?php
/**
 *
 * Package: Folio Authoring for WordPress
 * Class : Content
 * Description: This class contains content specific parameters and functions.
 */
 
namespace DPSFolioAuthor;
 
if( $_SERVER[ 'SCRIPT_FILENAME' ] == __FILE__ )
	die( 'Access denied.' );

if(!class_exists('DPSFolioAuthor\Content')) { 
    
    class Content extends Entity{
	    
	    public $title = '';
	    public $shortTitle = '';
	    public $abstract = '';
	    public $shortAbstract = '';
	    public $keywords = array();
	    public $internalKeywords = array();
	    public $department = '';
	    public $category = '';
	    public $importance = 'normal';
	    public $socialShareUrl = '';
	    public $availabilityDate = '';
	    public $_links = array();
	    public $isPublishable = true;
	    
	    public $socialSharing; // image url
	    public $thumbnail; // image url
	    public $productIds;
	    public $collections; // list of collections that content belongs to (not setable)

	    // CMS
		public $device; // if this entity is associated with a device (rendition)
		

	    public function __construct($data = array()) {
			parent::__construct($data);
		}
		
		public function add_to_collection($collectionId){
			
		}
				        
    } // END class Content 
}