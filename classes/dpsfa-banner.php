<?php
/**
 *
 * Package: Folio Authoring for WordPress
 * Class : Banner
 * Description: This class contains banner specific parameters and functions.
 */
 
namespace DPSFolioAuthor;
 
if( $_SERVER[ 'SCRIPT_FILENAME' ] == __FILE__ )
	die( 'Access denied.' );

if(!class_exists('DPSFolioAuthor\Banner')) { 
    
    class Banner extends Content{
	    
	    public $isAd = FALSE;
	    public $adType = 'static';
	    public $adCategory = '';
	    public $advertiser = '';
	    public $accessState = 'metered';
	    public $hideFromBrowsePage = FALSE;
	    public $bannerTapAction = "none"; // `none` or `webLink`
	    public $isTrustedContent = FALSE;

	    // CMS Specific
	    
	    public function __construct($data = array()) {
			parent::__construct();
			
			$this->entityType = 'banner';
			
			if(!empty($data['id'])){
				$CMS = new CMS();
				$this->id = $data['id'];
				$this->populate_object( $CMS->get_entity_data($this) );
			}
			$this->populate_object($data);
		}
				
		public $apiAllowed = array(
		    'isAd', 
		    'adType', 
		    'adCategory', 
		    'advertiser', 
		    'accessState', 
		    'hideFromBrowsePage', 
		    'isTrustedContent', 
		    'entityType', 
		    'entityName', 
		    //'version', 
		    //'entityId', 
		    //'url', 
		    //'modified', 
		    //'created', 
		    //'userData', 
		    'title', 
		    '_links',
		    'shortTitle', 
		    'abstract', 
		    'shortAbstract', 
		    //'keywords',  // TODO: tags coming from angular - serialize
		    //'internalKeywords', 
		    'department', 
		    'category', 
		    'importance', 
		    'socialShareUrl', 
		    'availabilityDate'
		);
			        
    } // END class Banner 
}