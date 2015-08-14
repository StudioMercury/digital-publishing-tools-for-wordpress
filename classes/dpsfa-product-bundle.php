<?php
/**
 *
 * Package: Folio Authoring for WordPress
 * Class : ProductBundle
 * Description: This class contains product bundle specific parameters and functions.
 */
 
namespace DPSFolioAuthor;
 
if( $_SERVER[ 'SCRIPT_FILENAME' ] == __FILE__ )
	die( 'Access denied.' );

if(!class_exists('DPSFolioAuthor\ProductBundle')) { 
    
    class ProductBundle {
	    
	    public $label = '';
	    public $description = '';
	    public $bundleType = 'SUBSCRIPTION';
	    public $strategy = '*';
	    public $entitlementPermanence = 'ACCESS'; // `OWNED` or `ACCESS`
	    public $id = '';

	    // CMS Specific
	    
	    public function __construct($data = array()) {
			parent::__construct();
			
			$this->entityType = 'bundle';
			
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
			        
    } // END class ProductBundle 
}